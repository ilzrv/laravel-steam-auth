<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/steam-auth.php', 'steam-auth');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/steam-auth.php' => $this->app->configPath('steam-auth.php'),
        ]);
    }
}
