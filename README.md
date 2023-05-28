# Steam Auth for Laravel
[![Latest Stable Version](https://img.shields.io/packagist/v/ilzrv/laravel-steam-auth.svg)](https://packagist.org/packages/ilzrv/laravel-steam-auth)
[![Codecov](https://img.shields.io/codecov/c/github/ilzrv/laravel-steam-auth?token=MIEA87EZGP)](https://app.codecov.io/github/ilzrv/laravel-steam-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/ilzrv/laravel-steam-auth.svg)](https://packagist.org/packages/ilzrv/laravel-steam-auth)
[![License](https://img.shields.io/github/license/ilzrv/laravel-steam-auth.svg)](https://packagist.org/packages/ilzrv/laravel-steam-auth)

Package allows you to implement Steam authentication in your Laravel project.

## Requirements
 * Laravel 9+
 * PHP 8.1+

## Installation
#### Install the package
```bash
composer require ilzrv/laravel-steam-auth
```

#### Publish the config file
```bash
php artisan vendor:publish --provider="Ilzrv\LaravelSteamAuth\ServiceProvider"
```

#### Setup Steam API Key(s)

Add your Steam API key to your `.env` file. You can find it [here](https://steamcommunity.com/dev/apikey).

*if you want to use multiple API keys just list them separated by commas*

```
STEAM_AUTH_API_KEYS=YourSteamApiKey1,YourSteamApiKey2
```

## Tips

#### Client Settings
You can use any settings that your client supports, for example, a [Guzzle Proxy](https://docs.guzzlephp.org/en/latest/request-options.html#proxy):

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Http\Request;
use Ilzrv\LaravelSteamAuth\SteamAuthenticator;

public function __invoke(
    Request $request,
    HttpFactory $httpFactory,
): RedirectResponse {
    $client = new Client([
        'proxy' => 'socks5://user:password@192.168.1.1:1080',
    ]);

    $steamAuthenticator = new SteamAuthenticator(
        new Uri($request->getUri()),
        $client,
        $httpFactory,
    );
    
    // Continuation of your code...
}
```

#### Proxy Domain
If you want to make a proxy domain. Update `redirect_url` inside `steam-auth.php` to your absolute address like `https://auth.test/login`. You can use different domains for the local environment and for production like this:

```php
<?php

declare(strict_types=1);

return [
    'redirect_url' => env('APP_ENV', 'production') == 'production'
        ? 'https://auth.test/login'
        : null,
];
```

In the NGINX settings for proxy domain, you can specify the following:
```
server {
    listen 443 ssl http2;
    server_name auth.test;
    return 301 https://general.test$uri$is_args$args;
}
```

## Basic example

In `routes/web.php`:

```php
Route::get('login', \App\Http\Controllers\Auth\SteamAuthController::class);
```

Create a controller `SteamAuthController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Ilzrv\LaravelSteamAuth\Exceptions\Validation\ValidationException;
use Ilzrv\LaravelSteamAuth\SteamAuthenticator;
use Ilzrv\LaravelSteamAuth\SteamUserDto;

final class SteamAuthController
{
    public function __invoke(
        Request $request,
        Redirector $redirector,
        Client $client,
        HttpFactory $httpFactory,
    ): RedirectResponse {
        $steamAuthenticator = new SteamAuthenticator(
            new Uri($request->getUri()),
            $client,
            $httpFactory,
        );

        try {
            $steamAuthenticator->auth();
        } catch (ValidationException) {
            return $redirector->to(
                $steamAuthenticator->buildAuthUrl()
            );
        }

        $steamUser = $steamAuthenticator->getSteamUser();

        Auth::login(
            $this->firstOrCreate($steamUser),
            true
        );

        return $redirector->to('/');
    }

    private function firstOrCreate(SteamUserDto $steamUser): User
    {
        return User::firstOrCreate([
            'steam_id' => $steamUser->getSteamId(),
        ], [
            'name' => $steamUser->getPersonaName(),
            'avatar' => $steamUser->getAvatarFull(),
            'player_level' => $steamUser->getPlayerLevel(),
            // ...and other what you need
        ]);
    }
}
```
