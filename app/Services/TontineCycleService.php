<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Group;
use Carbon\Carbon;

class TontineCycleService
{
    public function ensureContributionsForCycle(Group $group, int $cycleNumber, Carbon|string|null $dueDate = null): void
    {
        $dueDate = $dueDate ? Carbon::parse($dueDate) : Carbon::parse($group->next_due_date ?? $group->start_date);

        $group->members()
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->each(function ($member) use ($group, $cycleNumber, $dueDate) {
                Contribution::firstOrCreate(
                    [
                        'group_id' => $group->id,
                        'user_id' => $member->user_id,
                        'cycle_number' => $cycleNumber,
                    ],
                    [
                        'amount_fcfa' => $group->contribution_amount,
                        'status' => 'pending',
                        'due_date' => $dueDate,
                    ]
                );
            });
    }
}
