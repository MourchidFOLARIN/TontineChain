<?php

namespace App\Console\Commands;

use App\Models\Contribution;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\TontineNotificationMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\MessageController;
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
            if (! $user) {
                Log::warning("Late contribution {$contrib->id} ignored: user not found.");
                continue;
            }

            $user->increment('score_confiance', $scoreImpact);

            // 4. Garantie Automatique (Si retard > 5 jours et assurance active)
            if ($lateDays >= 5 && $contrib->group->insurance_percent > 0) {
                $group = $contrib->group;
                if ($group->insurance_fund >= $contrib->amount_fcfa) {
                    $group->decrement('insurance_fund', $contrib->amount_fcfa);
                    $contrib->update([
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                        'mobile_money_provider' => 'insurance_fund',
                    ]);
                    
                    $this->notifications->notify(
                        $group->creator_id,
                        'insurance_activated',
                        "Alerte : Le fonds de garantie a couvert la cotisation de {$user->full_name} pour éviter l'arrêt du cycle."
                    );
                    Log::info("Insurance fund used for user {$user->id} in group {$group->id}");
                }
            }

            // 5. Notification Email + Chat (Social Pressure)
            if ($user->email) {
                Mail::to($user->email)
                    ->locale($user->preferred_language ?? 'fr')
                    ->queue(new TontineNotificationMail(
                        "ALERTE RETARD - TontineChaine",
                        __('messages.late_payment') . " ($lateDays jours)"
                    ));
            }

            // Message dans le chat du groupe (Transparence)
            MessageController::sendSystemMessage(
                $contrib->group_id,
                "⚠️ Rappel : La cotisation de " . $user->full_name . " est attendue. Le cycle est actuellement bloqué."
            );

            Log::warning("Late contribution processed for user {$user->id} in group {$contrib->group_id}");
        }

        $this->info('Done.');
    }
}
