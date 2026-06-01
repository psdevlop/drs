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
                'collaboration' => __('messages.eval_rating_collaboration'),
                'reliability' => __('messages.eval_rating_reliability'),
                'contribution' => __('messages.eval_rating_contribution'),
                'job_skills' => __('messages.eval_rating_job_skills'),
                'positive_influence' => __('messages.eval_rating_positive_influence'),
            ];
        }

        if ($type === 'manager') {
            $roleItems = match ($internRole) {
                'senior_programmer' => [
                    'technical_expertise' => [__('messages.eval_rating_technical_expertise'), 15],
                    'problem_solving' => [__('messages.eval_rating_problem_solving'), 15],
                    'code_review_mentoring' => [__('messages.eval_rating_code_review_mentoring'), 15],
                    'deliverable_quality' => [__('messages.eval_rating_deliverable_quality'), 15],
                ],
                'mid_programmer' => [
                    'core_technical_skills' => [__('messages.eval_rating_core_technical_skills'), 15],
                    'learning_speed' => [__('messages.eval_rating_learning_speed'), 15],
                    'requirement_understanding' => [__('messages.eval_rating_requirement_understanding'), 15],
                    'output_quality' => [__('messages.eval_rating_output_quality'), 15],
                ],
                'translator' => [
                    'translation_accuracy' => [__('messages.eval_rating_translation_accuracy'), 15],
                    'technical_terminology' => [__('messages.eval_rating_technical_terminology'), 15],
                    'cultural_awareness' => [__('messages.eval_rating_cultural_awareness'), 15],
                    'speed_realtime' => [__('messages.eval_rating_speed_realtime'), 15],
                ],
                default => [],
            };
            $commonItems = [
                'reliability_diligence' => [__('messages.eval_rating_reliability_diligence'), 10],
                'collaboration_communication' => [__('messages.eval_rating_collaboration_communication'), 10],
                'learning_growth' => [__('messages.eval_rating_learning_growth'), 10],
                'cultural_fit' => [__('messages.eval_rating_cultural_fit'), 10],
            ];
            return $roleItems + $commonItems;
        }

        return [];
    }

    public static function frequencyLabels(): array
    {
        return [
            'daily' => __('messages.eval_frequency_daily'),
            '2-3x_week' => __('messages.eval_frequency_2_3x_week'),
            'weekly' => __('messages.eval_frequency_weekly'),
            'rarely' => __('messages.eval_frequency_rarely'),
        ];
    }

    public static function rehireRecommendationLabels(): array
    {
        return [
            'strongly_recommend' => __('messages.eval_rehire_strongly_recommend'),
            'recommend' => __('messages.eval_rehire_recommend'),
            'conditional' => __('messages.eval_rehire_conditional'),
            'do_not_recommend' => __('messages.eval_rehire_do_not_recommend'),
        ];
    }

    public static function salaryIncreaseLabels(): array
    {
        return [
            '0' => __('messages.eval_salary_0'),
            'under_5' => __('messages.eval_salary_under_5'),
            '5_10' => __('messages.eval_salary_5_10'),
            '10_15' => __('messages.eval_salary_10_15'),
            'over_15' => __('messages.eval_salary_over_15'),
        ];
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
