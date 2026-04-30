<?php

use App\Jobs\CleanExpiredCartsJob;
use App\Jobs\SyncProductViewsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SyncProductViewsJob)->hourly();
Schedule::job(new CleanExpiredCartsJob)->daily();
Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('activitylog:clean')->daily();
