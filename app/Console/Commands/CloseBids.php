<?php

namespace App\Console\Commands;

use App\Models\Bid;
use App\Models\Group;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseBids extends Command
{
    protected $signature = 'tontine:close-bids';
    protected $description = 'Close bidding windows and select the winning bid for the current cycle';

    public function handle()
    {
        $this->info('Checking active bidding groups...');

        $groups = Group::where('status', 'active')
            ->where('payout_method', 'bidding')
            ->get();

        foreach ($groups as $group) {
            $this->info("Processing group: {$group->name}");

            $hasWinner = Bid::where('group_id', $group->id)
                ->where('cycle_number', $group->current_cycle)
                ->where('status', 'won')
                ->exists();

            if ($hasWinner) {
                $this->line("A winning bid already exists for cycle {$group->current_cycle}.");
                continue;
            }

            $bestBid = Bid::where('group_id', $group->id)
                ->where('cycle_number', $group->current_cycle)
                ->where('status', 'pending')
                ->whereHas('user.memberships', function ($query) use ($group) {
                    $query->where('group_id', $group->id)
                        ->where('status', 'active')
                        ->where('has_received', false);
                })
                ->orderByDesc('discount_amount')
                ->orderBy('created_at')
                ->first();

            if (! $bestBid) {
                $this->warn("No eligible bid found for cycle {$group->current_cycle}.");
                continue;
            }

            DB::transaction(function () use ($group, $bestBid) {
                Bid::where('group_id', $group->id)
                    ->where('cycle_number', $group->current_cycle)
                    ->where('status', 'pending')
                    ->update(['status' => 'lost']);

                $bestBid->update(['status' => 'won']);

                Log::info("Winning bid selected: user {$bestBid->user_id}, cycle {$group->current_cycle}, group {$group->id}, discount {$bestBid->discount_amount} FCFA.");
                $this->info("Winning bid selected: user {$bestBid->user_id}, discount {$bestBid->discount_amount} FCFA.");
            });
        }

        $this->info('Bid closing complete.');
    }
}
