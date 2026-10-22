<?php

use App\Models\PhoneOtp;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Match saved searches against newly published listings (database + push notifications)
Schedule::command('aqar:notify-matching --hours=1')->hourly()->withoutOverlapping();
// Nightly database backup (keep storage/backups in your off-site backup rotation)
Schedule::command('aqar:backup')->dailyAt('02:30')->withoutOverlapping();
// Prune expired / consumed OTP codes
Schedule::call(fn () => PhoneOtp::where('expires_at', '<', now()->subDay())->delete())->daily();
