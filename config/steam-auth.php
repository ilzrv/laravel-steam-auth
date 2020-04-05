<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redirect URL
    |--------------------------------------------------------------------------
    |
    | By default redirect_url is equal to your login url.
    |
    | Can be relative url or null.
    |
    */
    'redirect_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Getting Steam User Level
    |--------------------------------------------------------------------------
    |
    | Defines need to get the user level.
    |
    | This makes an additional request in the Steam API.
    |
    */
    'getting_level' => true,

    /*
    |--------------------------------------------------------------------------
    | Steam API Keys
    |--------------------------------------------------------------------------
    |
    | List of Steam API keys for reducing
    | requests from a single key.
    |
    */
    'api_keys' => explode(',', env('STEAM_AUTH_API_KEYS')),

    /*
    |--------------------------------------------------------------------------
    | Number of tries
    |--------------------------------------------------------------------------
    |
    | Number of times to attempt for getting
    | data from Steam API.
    |
    */
    'tries' => 1,

];
