<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $tz = config('triase.timezone');
        config(['app.timezone' => $tz]);
        date_default_timezone_set($tz);
        Carbon::setLocale('id');
    }
}
