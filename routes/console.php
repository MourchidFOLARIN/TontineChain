<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('tontine:check-deadlines')->dailyAt('08:00');
Schedule::command('tontine:send-reminders')->dailyAt('09:00');
