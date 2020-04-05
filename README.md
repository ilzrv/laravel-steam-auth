# Steam Auth for Laravel 7

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

Add your Steam API key to your `.env` file. You can find it [here](http://steamcommunity.com/dev/apikey).

*if you want to use multiple API keys just list them separated by commas*

```
STEAM_AUTH_API_KEYS=YourSteamApiKey1,YourSteamApiKey2
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
