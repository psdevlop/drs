<?php

namespace App\Console\Commands;

use App\Models\OnCallRotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCreateOnCallRotation extends Command
{
    protected $signature = 'oncall:auto-rotate';
    protected $description = 'Automatically create a new on-call rotation after the previous one has completed';

    public function handle(): int
    {
        $completedRotations = OnCallRotation::where('is_active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->startOfDay())
            ->get();

        if ($completedRotations->isEmpty()) {
            $this->info('No completed rotations found.');
            return self::SUCCESS;
        }

        $userEmails = ['ps@drs.com', 'rk@drs.com', 'rose@drs.com'];
        $users = User::whereIn('email', $userEmails)->get()
            ->sortBy(fn ($user) => array_search($user->email, $userEmails))
            ->values();

        if ($users->count() < 2) {
            $this->error('Not enough users found for rotation. Need at least 2 users.');
            return self::FAILURE;
        }

        foreach ($completedRotations as $completedRotation) {
            $newStartDate = $completedRotation->end_date->copy()->addDay();

            // Calculate new end_date: same duration as the completed rotation
            $previousDuration = $completedRotation->start_date->diffInDays($completedRotation->end_date);
            $newEndDate = $newStartDate->copy()->addDays($previousDuration);

            // Check for overlap with any existing rotation
            $overlap = OnCallRotation::where('id', '!=', $completedRotation->id)
                ->where('start_date', '<=', $newEndDate->format('Y-m-d'))
                ->where(function ($q) use ($newStartDate) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $newStartDate->format('Y-m-d'));
                })
                ->exists();

            if ($overlap) {
                $this->warn("Skipping: overlapping rotation exists for dates {$newStartDate->format('Y-m-d')} to {$newEndDate->format('Y-m-d')}.");
                continue;
            }

            // Deactivate the completed rotation
            $completedRotation->update(['is_active' => false]);

            // Create new rotation
            $newRotation = OnCallRotation::create([
                'name' => 'On Call Rotation - ' . $newStartDate->format('M d, Y'),
                'cycle_type' => 'weekly',
                'cycle_length' => 1,
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
                'is_active' => true,
                'notes' => 'Auto-created after completion of: ' . $completedRotation->name,
                'created_by' => $completedRotation->created_by,
            ]);

            foreach ($users as $order => $user) {
                $newRotation->users()->attach($user->id, ['order' => $order]);
            }

            $this->info("Created new rotation '{$newRotation->name}' from {$newStartDate->format('Y-m-d')} to {$newEndDate->format('Y-m-d')}.");
        }

        return self::SUCCESS;
    }
}
