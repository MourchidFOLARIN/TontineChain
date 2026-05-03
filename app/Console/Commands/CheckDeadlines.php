<?php

namespace App\Console\Commands;

use App\Models\Contribution;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\NotificationService;

class CheckDeadlines extends Command
{
    protected $signature = 'tontine:check-deadlines';
    protected $description = 'Check for late contributions and apply penalties';

    protected $notifications;

    public function __construct(NotificationService $notifications)
    {
        parent::__construct();
        $this->notifications = $notifications;
    }

    public function handle()
    {
        $this->info('Checking deadlines...');

        $lateContributions = Contribution::whereIn('status', ['pending', 'processing'])
            ->where('due_date', '<', Carbon::now())
            ->get();

        foreach ($lateContributions as $contrib) {
            $lateDays = Carbon::now()->diffInDays($contrib->due_date);

            // 1. Update status
            $contrib->update([
                'is_late' => true,
                'late_days' => $lateDays,
                'status' => 'late'
            ]);

            // 2. Log incident
            $scoreImpact = $lateDays <= 3 ? -5 : ($lateDays <= 7 ? -10 : -20);
            
            Incident::create([
                'group_id' => $contrib->group_id,
                'user_id' => $contrib->user_id,
                'type' => 'late_payment',
                'description' => "Retard de $lateDays jour(s) - Cycle {$contrib->cycle_number}",
                'cycle_number' => $contrib->cycle_number,
                'score_impact' => $scoreImpact
            ]);

            // 3. Update User Score
            $user = User::find($contrib->user_id);
            $user->increment('score_confiance', $scoreImpact);

            // 4. Notify User
            $this->notifications->notify(
                $user->id,
                'late_warning',
                "TontineChain: Votre cotisation est en retard de $lateDays jour(s). Régularisez pour éviter un malus."
            );

            Log::warning("Late contribution processed for user {$user->id} in group {$contrib->group_id}");
        }

        $this->info('Done.');
    }
}
