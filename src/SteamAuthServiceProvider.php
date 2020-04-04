<?php

namespace Ilzrv\LaravelSteamAuth;

use Illuminate\Support\ServiceProvider;

class SteamAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/config.php' => config_path('steam-auth.php')]);
    }
}
