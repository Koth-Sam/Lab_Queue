<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule the SendWeeklyDashboardReport command
Schedule::command('app:send-weekly-dashboard-report')
    ->weeklyOn(1, '9:00') // Run every Monday at 8:00 AM
    ->description('Send weekly dashboard reports to lecturers');