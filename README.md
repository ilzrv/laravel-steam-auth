# Steam Auth for Laravel
[![Latest Stable Version](https://img.shields.io/packagist/v/ilzrv/laravel-steam-auth.svg)](https://packagist.org/packages/ilzrv/laravel-steam-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/ilzrv/laravel-steam-auth.svg)](https://packagist.org/packages/ilzrv/laravel-steam-auth)
[![License](https://img.shields.io/github/license/ilzrv/laravel-steam-auth.svg)](https://packagist.org/packages/ilzrv/laravel-steam-auth)

Package allows you to implement Steam authentication in your Laravel project.

## Requirements
 * Laravel 7+
 * Guzzle HTTP 6.5+

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

#### PendingRequest Settings
You can use any of the Laravel PendingRequest settings. Proxies and retries for example

```php
public function __construct(Request $request)
{
    $pendingRequest = Http::withOptions([
        'proxy' => 'http://username:password@ip',
        'connect_timeout' => 10,
    ])->retry(3);

    $this->steamAuth = new SteamAuth($request, $pendingRequest);
}
```

#### Proxy Domain
If you want to make a proxy domain. Update `redirect_url` inside `steam-auth.php` to your absolute address like `https://auth.test/login`. You can use different domains for the local environment and for production like this:

```php
<?php

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

## Example

In `routes/web.php`:

```php
Route::get('login', 'Auth\SteamAuthController@login');
```

Create a controller `SteamAuthController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Support\Facades\Auth;
use Ilzrv\LaravelSteamAuth\SteamAuth;
use Ilzrv\LaravelSteamAuth\SteamData;

class SteamAuthController extends Controller
{
    /**
     * The SteamAuth instance.
     *
     * @var SteamAuth
     */
    protected $steamAuth;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * SteamAuthController constructor.
     *
     * @param SteamAuth $steamAuth
     */
    public function __construct(SteamAuth $steamAuth)
    {
        $this->steamAuth = $steamAuth;
    }

    /**
     * Get user data and login
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login()
    {
        if (!$this->steamAuth->validate()) {
            return $this->steamAuth->redirect();
        }

        $data = $this->steamAuth->getUserData();

        if (is_null($data)) {
            return $this->steamAuth->redirect();
        }

        Auth::login(
            $this->firstOrCreate($data),
            true
        );

        return redirect($this->redirectTo);
    }

    /**
     * Get the first user by SteamID or create new
     *
     * @param SteamData $data
     * @return User|\Illuminate\Database\Eloquent\Model
     */
    protected function firstOrCreate(SteamData $data)
    {
        return User::firstOrCreate([
            'steam_id' => $data->getSteamId(),
        ], [
            'name' => $data->getPersonaName(),
            'avatar' => $data->getAvatarFull(),
            'player_level' => $data->getPlayerLevel(),
            // ...and other what you need
        ]);
    }
}
```
