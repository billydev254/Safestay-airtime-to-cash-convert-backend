<?php

namespace App\Providers;

use App\Services\AirtimeIntake\IntakeInterface;
use App\Services\AirtimeIntake\ManualIntake;
use App\Services\Daraja\DarajaManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DarajaManager::class);
        $this->app->bind(IntakeInterface::class, ManualIntake::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
