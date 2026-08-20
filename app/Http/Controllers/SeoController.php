<?php

namespace App\Http\Controllers;

use App\Models\SeoKeyword;
use App\Models\SerpRank;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class SeoController extends Controller
{
    // Snapshots are stamped on the site's local (Korean) calendar dates.
    private const TZ = 'Asia/Seoul';

    /**
     * List every tracked keyword with its latest standing: where the owned
     * website ranks, and the best-ranking property across all tracked links.
     */
    public function index()
    {
        $latest = SerpRank::max('date');
        $keywords = $this->keywords();
        $websiteUrl = $this->websiteUrl();
        $labels = $this->urlLabels();

        $rows = $latest
            ? SerpRank::query()
                ->whereDate('date', $latest)
                ->get(['keyword', 'url', 'position'])
                ->groupBy('keyword')
            : collect();

        $items = $keywords->map(function (string $keyword) use ($rows, $websiteUrl, $labels) {
            $group = $rows->get($keyword) ?? collect();

            $websiteRank = optional($group->firstWhere('url', $websiteUrl))->position;

            // Best (lowest) rank across properties that are actually ranking.
            $best = $group->whereNotNull('position')->sortBy('position')->first();

            return [
                'keyword' => $keyword,
                'website_rank' => $websiteRank,
                'best_rank' => $best->position ?? null,
                'best_property' => $best ? ($labels[$best->url] ?? $best->url) : null,
            ];
        });

        return view('seo.index', [
            'keywords' => $items,
            'latestDate' => $latest,
        ]);
    }

    /**
     * Rank-over-time charts: one line per keyword for the website, a per-keyword
     * breakdown by property, and a latest-snapshot keyword × property table.
     */
    public function performance(Request $request)
    {
        [$defaultStart, $defaultEnd] = $this->defaultWindow();
        $start = $this->parseDate($request->input('start_date')) ?? $defaultStart;
        $end = $this->parseDate($request->input('end_date')) ?? $defaultEnd;
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $keywords = $this->keywords();
        $websiteUrl = $this->websiteUrl();
        $urlToLabel = $this->urlToLabel();          // distinct property URL => merged label
        $labels = $urlToLabel->all();

        $selectedKeyword = $request->input('keyword');
        if (! $keywords->contains($selectedKeyword)) {
            $selectedKeyword = $keywords->first();
        }

        $dateAxis = [];
        for ($d = $start; $d <= $end; $d = $d->addDay()) {
            $dateAxis[] = $d->toDateString();
        }

        // All rows in the window, indexed for fast lookup by (keyword,url,date).
        $rows = empty($dateAxis)
            ? collect()
            : SerpRank::query()
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereIn('keyword', $keywords->all())
                ->get(['date', 'keyword', 'url', 'position']);

        $byDate = $rows->groupBy(fn ($r) => $this->seriesKey(
            $r->keyword,
            $r->url,
            CarbonImmutable::parse($r->date)->toDateString(),
        ))->map(fn ($g) => $g->first());

        // Chart 1: one line per keyword = the website's rank over time.
        $websiteDatasets = $keywords->map(fn (string $kw) => [
            'label' => $kw,
            'position' => $this->series($kw, $websiteUrl, $dateAxis, $byDate),
        ])->all();

        // Chart 2: for the selected keyword, one line per distinct property.
        $keywordDatasets = $urlToLabel->map(fn (string $label, string $url) => [
            'label' => $label,
            'position' => $selectedKeyword ? $this->series($selectedKeyword, $url, $dateAxis, $byDate) : [],
        ])->values()->all();

        // Latest-snapshot breakdown table: keyword × property.
        $latestInWindow = $rows->max('date');
        $latestKey = $latestInWindow ? CarbonImmutable::parse($latestInWindow)->toDateString() : null;
        $breakdown = $keywords->map(fn (string $kw) => [
            'keyword' => $kw,
            'cells' => $urlToLabel->keys()->map(fn (string $url) => $latestKey
                ? optional($byDate->get($this->seriesKey($kw, $url, $latestKey)))->position
                : null)->all(),
        ]);

        // KPIs (website-centric, at the latest day in the window).
        $websiteLatest = $latestKey
            ? $keywords->map(fn (string $kw) => optional($byDate->get($this->seriesKey($kw, $websiteUrl, $latestKey)))->position)
            : collect();
        $ranked = $websiteLatest->filter(fn ($p) => $p !== null);

        $kpis = [
            'keywords' => $keywords->count(),
            'in_top_10' => $ranked->filter(fn ($p) => $p <= 10)->count(),
            'avg_rank' => $ranked->isNotEmpty() ? round($ranked->avg(), 1) : null,
            'best_rank' => $ranked->isNotEmpty() ? $ranked->min() : null,
        ];

        return view('seo.performance', [
            'chartWebsite' => ['labels' => $dateAxis, 'datasets' => $websiteDatasets],
            'chartKeyword' => ['labels' => $dateAxis, 'datasets' => $keywordDatasets],
            'kpis' => $kpis,
            'keywords' => $keywords,
            'selectedKeyword' => $selectedKeyword,
            'propertyLabels' => array_values($labels),
            'breakdown' => $breakdown,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'hasData' => $rows->whereNotNull('position')->isNotEmpty(),
            'isFiltered' => $request->hasAny(['start_date', 'end_date', 'keyword']),
        ]);
    }

    /** Build a column of positions aligned to $dateAxis (null where no snapshot). */
    private function series(string $keyword, string $url, array $dateAxis, Collection $byDate): array
    {
        return array_map(
            fn (string $day) => optional($byDate->get($this->seriesKey($keyword, $url, $day)))->position,
            $dateAxis,
        );
    }

    private function seriesKey(string $keyword, string $url, string $day): string
    {
        return $keyword."\x1f".$url."\x1f".$day;
    }

    /** Tracked keywords: config ∪ active DB keywords (imported campaigns). */
    private function keywords(): Collection
    {
        return SeoKeyword::tracked();
    }

    /** The owned website's tracked URL (links entry keyed 'website'). */
    private function websiteUrl(): ?string
    {
        $website = collect(config('seo.links', []))->firstWhere('key', 'website');

        return $website['url'] ?? collect(config('seo.links', []))->value('url');
    }

    /**
     * Distinct property URL => locale-aware merged label. Links sharing a URL
     * (X / Twitter) collapse into one "X / Twitter" series.
     */
    private function urlToLabel(): Collection
    {
        return $this->links()
            ->groupBy('url')
            ->map(fn ($group) => $group->pluck('label')->implode(' / '));
    }

    /** Property URL => single label (first link with that URL). */
    private function urlLabels(): array
    {
        return $this->urlToLabel()->all();
    }

    /**
     * Configured links with a locale-aware label: messages.seo_link_<key> when a
     * translation exists, otherwise the raw config label.
     */
    private function links(): Collection
    {
        return collect(config('seo.links', []))->map(function ($link) {
            $key = 'messages.seo_link_'.($link['key'] ?? '');

            return array_merge($link, [
                'label' => Lang::has($key) ? __($key) : ($link['label'] ?? ''),
            ]);
        });
    }

    /** Default trailing-month window ending on the latest day with data. */
    private function defaultWindow(): array
    {
        $windowDays = max(7, (int) config('seo.window_days', 31));
        $latest = SerpRank::max('date');
        $end = $latest
            ? CarbonImmutable::parse($latest, self::TZ)
            : CarbonImmutable::now(self::TZ)->subDay();

        return [$end->subDays($windowDays - 1), $end];
    }

    /** Parse a Y-m-d filter value into a local date, or null when absent/invalid. */
    private function parseDate(?string $value): ?CarbonImmutable
    {
        if (! $value) {
            return null;
        }
        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value, self::TZ)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
