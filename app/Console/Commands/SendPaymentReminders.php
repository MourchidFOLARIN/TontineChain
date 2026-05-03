<?php

namespace App\Console\Commands;

use App\Models\Contribution;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    protected $signature = 'tontine:send-reminders';
    protected $description = 'Send payment reminders to members 48h and 24h before due date';

    protected $notifications;

    public function __construct(NotificationService $notifications)
    {
        parent::__construct();
        $this->notifications = $notifications;
    }

    public function handle()
    {
        $this->info('Sending reminders...');

        $upcoming = Contribution::where('status', 'pending')
            ->whereBetween('due_date', [Carbon::now(), Carbon::now()->addHours(48)])
            ->get();

        foreach ($upcoming as $contrib) {
            $hoursLeft = Carbon::now()->diffInHours($contrib->due_date);
            
            $this->notifications->notify(
                $contrib->user_id,
                'payment_due',
                "TontineChain: Rappel ! Votre cotisation pour le groupe est due dans {$hoursLeft} heures."
            );
            
            Log::info("Sending reminder to user {$contrib->user_id} for group {$contrib->group_id}. Due in {$hoursLeft}h.");
        }

        $this->info('Done.');
    }
}
