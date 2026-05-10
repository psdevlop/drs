<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EvaluationController extends Controller
{
    public const DEADLINE = '2026-05-11';

    public function index()
    {
        $me = auth()->user();
        $cohort = User::whereNotNull('team_role')->orderBy('name')->get();

        $myEvaluations = Evaluation::where('evaluator_id', $me->id)
            ->get()
            ->keyBy(fn ($e) => $e->type . ':' . $e->evaluee_id);

        $superior = [];
        $peer = [];
        $self = null;

        if ($me->isDirector()) {
            foreach ($cohort as $person) {
                if ($person->id === $me->id) continue;
                if (!in_array($person->team_role, ['team_manager', 'team_member'], true)) continue;
                $superior[] = $this->taskRow('manager', $person, $myEvaluations);
            }
        } elseif ($me->isTeamManager()) {
            foreach ($cohort as $person) {
                if ($person->team_role === 'team_member') {
                    $superior[] = $this->taskRow('manager', $person, $myEvaluations);
                }
            }
            $self = $this->taskRow('self', $me, $myEvaluations);
            foreach ($cohort as $person) {
                if ($person->id === $me->id) continue;
                if (in_array($person->team_role, ['team_manager', 'team_member'], true)) {
                    $peer[] = $this->taskRow('peer', $person, $myEvaluations);
                }
            }
        } elseif ($me->isTeamMember()) {
            $self = $this->taskRow('self', $me, $myEvaluations);
            foreach ($cohort as $person) {
                if ($person->id === $me->id) continue;
                if (in_array($person->team_role, ['team_manager', 'team_member'], true)) {
                    $peer[] = $this->taskRow('peer', $person, $myEvaluations);
                }
            }
        }

        $totalForms = count($superior) + count($peer) + ($self ? 1 : 0);
        $completedForms = collect($superior)->where('completed', true)->count()
            + collect($peer)->where('completed', true)->count()
            + ($self && $self['completed'] ? 1 : 0);

        $deadline = \Carbon\Carbon::parse(self::DEADLINE);
        $daysRemaining = max(0, (int) round(now()->diffInDays($deadline, false)));

        $canViewResults = $me->isDirector() || $me->isTeamManager() || $me->isAdmin();
        $resultsSummary = [];
        if ($canViewResults) {
            foreach ($cohort->where('team_role', 'team_member') as $intern) {
                $hasManager = Evaluation::where('evaluee_id', $intern->id)->where('type', 'manager')->exists();
                $hasSelf = Evaluation::where('evaluee_id', $intern->id)->where('type', 'self')->exists();
                $peerCount = Evaluation::where('evaluee_id', $intern->id)->where('type', 'peer')->count();
                $resultsSummary[] = [
                    'user' => $intern,
                    'has_data' => $hasManager || $hasSelf || $peerCount > 0,
                ];
            }
        }

        return view('evaluations.index', compact(
            'me', 'superior', 'peer', 'self', 'totalForms', 'completedForms',
            'deadline', 'daysRemaining', 'canViewResults', 'resultsSummary'
        ));
    }

    private function taskRow(string $type, User $person, $myEvaluations): array
    {
        $key = $type . ':' . $person->id;
        $evaluation = $myEvaluations[$key] ?? null;
        return [
            'type' => $type,
            'person' => $person,
            'completed' => $evaluation !== null,
            'evaluation' => $evaluation,
        ];
    }

    public function create(Request $request, string $type, User $user)
    {
        $this->authorizeForm($type, $user);

        $existing = Evaluation::where('evaluator_id', auth()->id())
            ->where('evaluee_id', $user->id)
            ->where('type', $type)
            ->first();

        if ($existing) {
            return redirect()->route('evaluations.show', $existing)
                ->with('info', 'You have already submitted this evaluation. Use Edit to modify it.');
        }

        $internRole = $user->intern_role;
        $ratingItems = Evaluation::ratingItems($type, $internRole);
        $evaluation = null;
        $mode = 'create';

        return view($this->formView($type), compact('user', 'type', 'ratingItems', 'internRole', 'evaluation', 'mode'));
    }

    public function store(Request $request, string $type, User $user)
    {
        $this->authorizeForm($type, $user);

        $existing = Evaluation::where('evaluator_id', auth()->id())
            ->where('evaluee_id', $user->id)
            ->where('type', $type)
            ->first();
        if ($existing) {
            return redirect()->route('evaluations.show', $existing)
                ->with('info', 'You have already submitted this evaluation.');
        }

        $data = $this->validateAndExtract($request, $type, $user);
        $data['evaluator_id'] = auth()->id();
        $data['evaluee_id'] = $user->id;
        $data['type'] = $type;
        $data['intern_role'] = $user->intern_role;
        $data['submitted_at'] = now();

        Evaluation::create($data);

        return redirect()->route('evaluations.index')
            ->with('success', 'Evaluation submitted successfully.');
    }

    public function edit(Evaluation $evaluation)
    {
        $this->authorizeOwn($evaluation);
        $user = $evaluation->evaluee;
        $type = $evaluation->type;
        $internRole = $evaluation->intern_role;
        $ratingItems = Evaluation::ratingItems($type, $internRole);
        $mode = 'edit';

        return view($this->formView($type), compact('user', 'type', 'ratingItems', 'internRole', 'evaluation', 'mode'));
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $this->authorizeOwn($evaluation);
        $data = $this->validateAndExtract($request, $evaluation->type, $evaluation->evaluee);
        $data['submitted_at'] = now();
        // Editing resets confirmation
        $data['confirmed_at'] = null;
        $data['confirmed_by_id'] = null;
        $evaluation->update($data);

        return redirect()->route('evaluations.show', $evaluation)
            ->with('success', 'Evaluation updated successfully.');
    }

    public function destroy(Evaluation $evaluation)
    {
        $me = auth()->user();
        if ($evaluation->evaluator_id !== $me->id && !$me->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN);
        }
        $evaluation->delete();

        return redirect()->back()->with('success', 'Evaluation deleted.');
    }

    public function confirm(Evaluation $evaluation)
    {
        $me = auth()->user();
        if (!$me->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'Only admins can confirm evaluations.');
        }
        $evaluation->confirmed_at = now();
        $evaluation->confirmed_by_id = $me->id;
        $evaluation->save();

        return redirect()->back()->with('success', 'Evaluation confirmed.');
    }

    public function show(Evaluation $evaluation)
    {
        $me = auth()->user();
        $isOwn = $evaluation->evaluator_id === $me->id;
        if (!$isOwn && !$me->isAdmin() && !$me->isDirector() && !$me->isTeamManager()) {
            abort(Response::HTTP_FORBIDDEN);
        }
        $evaluation->load(['evaluator', 'evaluee', 'confirmedBy']);
        $ratingItems = Evaluation::ratingItems($evaluation->type, $evaluation->intern_role);
        return view('evaluations.show', compact('evaluation', 'ratingItems'));
    }

    public function adminIndex(Request $request)
    {
        $interns = User::where('team_role', 'team_member')->orderBy('name')->get();

        $matrix = [];
        foreach ($interns as $intern) {
            $received = Evaluation::with(['evaluator', 'confirmedBy'])
                ->where('evaluee_id', $intern->id)
                ->orderBy('type')
                ->get();

            $self = $received->firstWhere('type', 'self');
            $peers = $received->where('type', 'peer')->values();
            $managers = $received->where('type', 'manager')->values();

            $managerScore = $managers->isNotEmpty()
                ? round($managers->avg(fn ($e) => $e->weightedScore() ?? 0), 2)
                : null;
            $peerAvg = $peers->isNotEmpty()
                ? round($peers->avg(fn ($e) => $e->averageRating() ?? 0), 2)
                : null;
            $selfScore = $self?->self_score !== null ? (float) $self->self_score : null;

            $composite = null;
            if ($managerScore !== null && $peerAvg !== null && $selfScore !== null) {
                $composite = round(($managerScore * 0.5) + ($peerAvg * 0.3) + ($selfScore * 0.2), 2);
            }

            $matrix[] = [
                'intern' => $intern,
                'self' => $self,
                'peers' => $peers,
                'managers' => $managers,
                'self_score' => $selfScore,
                'peer_avg' => $peerAvg,
                'manager_score' => $managerScore,
                'composite' => $composite,
                'grade' => $this->grade($composite),
            ];
        }

        return view('evaluations.admin', compact('matrix'));
    }

    private function grade(?float $score): ?string
    {
        if ($score === null) return null;
        if ($score >= 4.5) return 'S';
        if ($score >= 4.0) return 'A';
        if ($score >= 3.5) return 'B';
        if ($score >= 3.0) return 'C';
        return 'D';
    }

    private function formView(string $type): string
    {
        return match ($type) {
            'self' => 'evaluations.self',
            'peer' => 'evaluations.peer',
            'manager' => 'evaluations.manager',
        };
    }

    private function authorizeForm(string $type, User $evaluee): void
    {
        $me = auth()->user();

        if (!in_array($type, ['self', 'peer', 'manager'], true)) {
            abort(404);
        }

        if ($me->isSuperAdmin()) {
            return; // super admin can fill anything
        }

        if ($type === 'self') {
            if ($evaluee->id !== $me->id || !in_array($me->team_role, ['team_manager', 'team_member'], true)) {
                abort(Response::HTTP_FORBIDDEN, 'Self-assessment is only available for team members and team managers.');
            }
        } elseif ($type === 'peer') {
            $allowed = in_array($me->team_role, ['team_manager', 'team_member'], true)
                && in_array($evaluee->team_role, ['team_manager', 'team_member'], true)
                && $evaluee->id !== $me->id;
            if (!$allowed) {
                abort(Response::HTTP_FORBIDDEN, 'Peer reviews are between team members and the team manager.');
            }
        } elseif ($type === 'manager') {
            if ($me->isDirector()) {
                if (!in_array($evaluee->team_role, ['team_manager', 'team_member'], true)) {
                    abort(Response::HTTP_FORBIDDEN, 'Director can only evaluate team manager and team members.');
                }
            } elseif ($me->isTeamManager()) {
                if ($evaluee->team_role !== 'team_member') {
                    abort(Response::HTTP_FORBIDDEN, 'Team manager can only evaluate team members.');
                }
            } else {
                abort(Response::HTTP_FORBIDDEN, 'Only the director or team manager can submit superior evaluations.');
            }
        }
    }

    private function authorizeOwn(Evaluation $evaluation): void
    {
        $me = auth()->user();
        if ($me->isSuperAdmin()) return;
        if ($evaluation->evaluator_id !== $me->id) {
            abort(Response::HTTP_FORBIDDEN, 'You can only modify evaluations you submitted.');
        }
    }

    private function validateAndExtract(Request $request, string $type, User $evaluee): array
    {
        $data = [];

        if ($type === 'self') {
            $validated = $request->validate([
                'self_score' => ['required', 'numeric', 'min:1', 'max:5'],
                'accomplishments' => ['required', 'string'],
                'challenge' => ['required', 'string'],
                'growth' => ['required', 'string'],
                'improvement_plan' => ['required', 'string'],
                'future_contribution' => ['required', 'string'],
            ]);
            $data['self_score'] = $validated['self_score'];
            $data['responses'] = [
                'accomplishments' => $validated['accomplishments'],
                'challenge' => $validated['challenge'],
                'growth' => $validated['growth'],
                'improvement_plan' => $validated['improvement_plan'],
                'future_contribution' => $validated['future_contribution'],
            ];
        } elseif ($type === 'peer') {
            $items = array_keys(Evaluation::ratingItems('peer'));
            $rules = [
                'frequency' => ['required', 'in:daily,2-3x_week,weekly,rarely'],
                'strengths' => ['required', 'string'],
                'growth_areas' => ['required', 'string'],
                'recollaborate' => ['required', 'string'],
                'manager_only_comments' => ['nullable', 'string'],
            ];
            foreach ($items as $key) {
                $rules['ratings.' . $key] = ['required', 'numeric', 'min:1', 'max:5'];
            }
            $validated = $request->validate($rules);
            $data['frequency'] = $validated['frequency'];
            $data['ratings'] = $validated['ratings'];
            $data['responses'] = [
                'strengths' => $validated['strengths'],
                'growth_areas' => $validated['growth_areas'],
                'recollaborate' => $validated['recollaborate'],
                'manager_only_comments' => $validated['manager_only_comments'] ?? '',
            ];
        } elseif ($type === 'manager') {
            $items = array_keys(Evaluation::ratingItems('manager', $evaluee->intern_role));
            $rules = [
                'key_achievements' => ['required', 'string'],
                'areas_for_improvement' => ['required', 'string'],
                'rehire_recommendation' => ['required', 'in:strongly_recommend,recommend,conditional,do_not_recommend'],
                'salary_increase' => ['required', 'in:0,under_5,5_10,10_15,over_15'],
            ];
            foreach ($items as $key) {
                $rules['ratings.' . $key] = ['required', 'numeric', 'min:1', 'max:5'];
            }
            $validated = $request->validate($rules);
            $data['ratings'] = $validated['ratings'];
            $data['rehire_recommendation'] = $validated['rehire_recommendation'];
            $data['salary_increase'] = $validated['salary_increase'];
            $data['responses'] = [
                'key_achievements' => $validated['key_achievements'],
                'areas_for_improvement' => $validated['areas_for_improvement'],
            ];
        }

        return $data;
    }
}
