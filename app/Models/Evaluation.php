<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = [
        'evaluator_id',
        'evaluee_id',
        'type',
        'intern_role',
        'ratings',
        'responses',
        'frequency',
        'self_score',
        'rehire_recommendation',
        'salary_increase',
        'submitted_at',
        'confirmed_at',
        'confirmed_by_id',
    ];

    protected function casts(): array
    {
        return [
            'ratings' => 'array',
            'responses' => 'array',
            'submitted_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_id');
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function evaluee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluee_id');
    }

    public static function ratingItems(string $type, ?string $internRole = null): array
    {
        if ($type === 'peer') {
            return [
                'collaboration' => 'Collaboration & Communication — Clarity and responsiveness in meetings, messaging, and documents',
                'reliability' => 'Reliability — Delivers committed work on time and at promised quality',
                'contribution' => 'Contribution — Tangible contribution to shared tasks and team projects',
                'job_skills' => 'Job Skills (Observable) — Level of role-specific skill observed while working together',
                'positive_influence' => 'Positive Influence — Effect on team morale; willingness to help others',
            ];
        }

        if ($type === 'manager') {
            $roleItems = match ($internRole) {
                'senior_programmer' => [
                    'technical_expertise' => ['Technical Expertise — Architecture design, code quality, fluency with libraries and tools', 15],
                    'problem_solving' => ['Problem Solving — Ability to analyze and resolve complex bugs and technical issues', 15],
                    'code_review_mentoring' => ['Code Review & Mentoring — Reviews others\' code; guides the mid-level intern', 15],
                    'deliverable_quality' => ['Deliverable Quality — Test coverage, documentation, maintainability', 15],
                ],
                'mid_programmer' => [
                    'core_technical_skills' => ['Core Technical Skills — Proficiency in assigned languages/frameworks; ability to write working code', 15],
                    'learning_speed' => ['Learning Speed — Speed of picking up new technologies and codebases', 15],
                    'requirement_understanding' => ['Requirement Understanding & Implementation — Accurately interprets and translates requirements into code', 15],
                    'output_quality' => ['Output Quality — Code readability, basic testing, bug frequency', 15],
                ],
                'translator' => [
                    'translation_accuracy' => ['Translation/Interpretation Accuracy — Faithfully conveys meaning and nuance; frequency of errors', 15],
                    'technical_terminology' => ['Technical Terminology — Appropriate handling of domain-specific terms; consistency', 15],
                    'cultural_awareness' => ['Cultural & Contextual Awareness — Bridges cultural differences; quality of mediation', 15],
                    'speed_realtime' => ['Speed & Real-time Performance — Throughput on documents/meetings; stability during live interpretation', 15],
                ],
                default => [],
            };
            $commonItems = [
                'reliability_diligence' => ['Reliability & Diligence — Meets deadlines, attendance, completion of committed deliverables', 10],
                'collaboration_communication' => ['Collaboration & Communication — Communication, information sharing, conflict handling', 10],
                'learning_growth' => ['Learning & Growth — Receptiveness to feedback, self-directed learning, growth over the period', 10],
                'cultural_fit' => ['Cultural Fit — Alignment with team values and culture; long-term retention potential', 10],
            ];
            return $roleItems + $commonItems;
        }

        return [];
    }

    public function weightedScore(): ?float
    {
        if ($this->type !== 'manager' || !is_array($this->ratings)) {
            return null;
        }
        $items = self::ratingItems('manager', $this->intern_role);
        $total = 0;
        foreach ($items as $key => [$label, $weight]) {
            $score = (float) ($this->ratings[$key] ?? 0);
            $total += $score * $weight;
        }
        return round($total / 100, 2);
    }

    public function averageRating(): ?float
    {
        if (!is_array($this->ratings) || empty($this->ratings)) {
            return null;
        }
        $values = array_filter(array_map('floatval', $this->ratings), fn ($v) => $v > 0);
        if (empty($values)) {
            return null;
        }
        return round(array_sum($values) / count($values), 2);
    }
}
