<?php

return [
    // Site name shown as the link label in the SEO menu.
    'site_name' => env('SEO_SITE_NAME', '아벤카지노'),

    // Where the site-name link points (the live website).
    'site_url' => env('SEO_SITE_URL', 'https://www.xn--py2bz5wr3gb9e.com/'),

    // Properties we track rankings for. For every keyword below, the daily sync
    // records where each of these URLs appears in Google's organic results.
    // 'key' must be unique (used for the locale label messages.seo_link_<key>).
    // Links that share a URL (X / Twitter) are merged into one tracked property.
    'links' => [
        ['key' => 'website',   'label' => 'Website',   'url' => 'https://www.xn--py2bz5wr3gb9e.com/'],
        ['key' => 'x',         'label' => 'X',         'url' => 'https://x.com/highkr01'],
        ['key' => 'instagram', 'label' => 'Instagram', 'url' => 'https://www.instagram.com/highcasino_official/'],
        ['key' => 'facebook',  'label' => 'Facebook',  'url' => 'https://www.facebook.com/profile.php?id=61590207599012'],
        ['key' => 'bandcamp',  'label' => 'Bandcamp',  'url' => 'https://highcasino.bandcamp.com/album/high-casino'],
        ['key' => 'twitter',   'label' => 'Twitter',   'url' => 'https://x.com/highkr01'],
    ],

    // Keywords tracked daily via SerpApi. Each keyword costs ONE SerpApi search
    // per day (one Google query), so the length of this list directly drives API
    // spend. SerpApi provides NO historical backfill — a newly added keyword only
    // starts charting from the first day it is synced. Edit freely (replace these
    // brand/seed terms with the ones you actually want to rank-track).
    'keywords' => array_values(array_filter(array_map('trim', explode(
        ',',
        env('SEO_KEYWORDS', '아벤카지노,하이카지노,온라인카지노,카지노사이트')
    )))),

    // ── SerpApi ───────────────────────────────────────────────────────────
    // Secret key — keep it in .env, never commit it here.
    'serpapi_key' => env('SERPAPI_KEY'),
    'serpapi_endpoint' => env('SERPAPI_ENDPOINT', 'https://serpapi.com/search'),

    // Google locale for the searches (the site's Korean audience).
    'gl' => env('SEO_GL', 'kr'),                  // country
    'hl' => env('SEO_HL', 'ko'),                  // interface language
    'google_domain' => env('SEO_GOOGLE_DOMAIN', 'google.co.kr'),
    'location' => env('SEO_LOCATION', 'South Korea'),
    'num' => (int) env('SEO_NUM', 100),           // results pulled per query (tracking horizon)

    // How a property URL is matched against an organic result:
    //   'host'  — match by host (strip scheme + leading www., lowercase, ignore
    //             path). A property usually ranks via a deep page, so exact-URL
    //             matching would almost always miss. The actual ranking URL is
    //             stored as matched_url for auditing.
    //   'exact' — match only the exact URL.
    'match_mode' => env('SEO_MATCH_MODE', 'host'),

    // Trailing days shown in the dashboard charts (at least one month).
    'window_days' => (int) env('SEO_WINDOW_DAYS', 31),

    // ── allin112 campaign import ────────────────────────────────────────────
    // seo:import-allin112 pulls a campaign's keywords into the tracker. allin112
    // sits behind Cloudflare's managed challenge, which blocks server requests —
    // paste a fresh cf_clearance cookie from a browser into ALLIN112_COOKIE.
    'allin112' => [
        'base_url' => env('ALLIN112_BASE_URL', 'https://allin112.com/adm/SEO/rank.php'),
        'pw' => env('ALLIN112_PW', ''), // admin path authenticates via session cookie, not pw
        'cid' => (int) env('ALLIN112_CID', 3),
        'cookie' => env('ALLIN112_COOKIE'),
    ],
];
