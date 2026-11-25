<?php

namespace App\Providers;

use App\Services\DiabetesScreeningService;
use App\Services\TriageClassifier;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TriageClassifier::class, function ($app) {
            return new TriageClassifier($app->make(DiabetesScreeningService::class));
        });
    }

    public function boot(): void
    {
        $tz = config('triase.timezone');
        config(['app.timezone' => $tz]);
        date_default_timezone_set($tz);
        Carbon::setLocale('id');
    }
}
