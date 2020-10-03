<?php

namespace Ilzrv\LaravelSteamAuth;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;

class SteamAuth
{
    /**
     * @var string
     */
    protected const OPENID_URL = 'https://steamcommunity.com/openid/login';

    /**
     * @var string
     */
    protected const STEAM_DATA_URL = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s';

    /**
     * @var string
     */
    protected const STEAM_LEVEL_URL = 'https://api.steampowered.com/IPlayerService/GetSteamLevel/v1/?key=%s&format=json&steamid=%s';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var PendingRequest
     */
    protected $pendingRequest;

    /**
     * @var string
     */
    protected $authUrl;

    /**
     * @var string
     */
    protected $steamId;

    /**
     * @var SteamData
     */
    protected $steamData;

    public function __construct(Request $request, PendingRequest $pendingRequest = null)
    {
        $this->request = $request;
        $this->pendingRequest = $pendingRequest ?: app(PendingRequest::class);
        $this->authUrl = $this->buildAuthUrl();
    }

    /**
     * Check Steam auth
     *
     * @return bool
     */
    public function validate()
    {
        if (!$this->requestIsValid()) {
            return false;
        }

        $response = $this->pendingRequest->get(self::OPENID_URL, $this->getRequestPrams());

        return $this->parseSteamId()
            && $this->parseSteamData()
            && preg_match("#is_valid\s*:\s*true#i", $response) === 1;
    }

    /**
     * Redirect user to Steam login page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        return redirect($this->getAuthUrl());
    }

    /**
     * Info about Steam logged in user
     *
     * @return SteamData
     */
    public function getUserData()
    {
        return $this->steamData;
    }

    /**
     * Return the Steam login url
     *
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->authUrl;
    }

    /**
     * Parse Steam ID from response
     *
     * @return bool
     */
    protected function parseSteamId()
    {
        preg_match(
            "#^https?://steamcommunity.com/openid/id/([0-9]{17,25})#",
            $this->request->get('openid_claimed_id'),
            $matches
        );

        if (is_numeric($matches[1])) {
            $this->steamId = $matches[1];
            return true;
        }

        return false;
    }

    /**
     * Parse Steam user data
     *
     * @return bool
     */
    protected function parseSteamData()
    {
        try {
            $userData = $this->pendingRequest->get($this->getSteamDataUrl())['response']['players'][0];

            if (config('steam-auth.getting_level')) {
                $userLevel = $this->pendingRequest->get($this->getSteamLevelUrl());
            }

            $this->steamData = new SteamData(
                array_merge($userData, $userLevel['response'] ?? [])
            );
        } catch (\Exception $e) {
            app('log')->error($e);
            return false;
        }

        return true;
    }

    /**
     * Get Steam data url with api key
     *
     * @return string
     */
    protected function getSteamDataUrl()
    {
        return sprintf(
            self::STEAM_DATA_URL,
            $this->getApiKey(),
            $this->steamId
        );
    }

    /**
     * Get Steam level url with api key
     *
     * @return string
     */
    protected function getSteamLevelUrl()
    {
        return sprintf(
            self::STEAM_LEVEL_URL,
            $this->getApiKey(),
            $this->steamId
        );
    }

    /**
     * Get random api key from list
     *
     * @return string
     */
    protected function getApiKey()
    {
        $apiKeys = config('steam-auth.api_keys');

        return $apiKeys[array_rand($apiKeys)];
    }

    /**
     * Get params for login request
     *
     * @return array
     */
    protected function getRequestPrams()
    {
        $params = [
            'openid.assoc_handle' => $this->request->get('openid_assoc_handle'),
            'openid.signed' => $this->request->get('openid_signed'),
            'openid.sig' => $this->request->get('openid_sig'),
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'check_authentication',
        ];

        $signed = explode(',', $this->request->get('openid_signed'));

        foreach ($signed as $item) {
            $value = $this->request->get('openid_' . str_replace('.', '_', $item));
            $params['openid.' . $item] = $value;
        }

        return $params;
    }

    /**
     * Build Steam Auth Url
     *
     * @return string
     */
    protected function buildAuthUrl()
    {
        $redirectUrl = config('steam-auth.redirect_url')
            ? url(config('steam-auth.redirect_url'))
            : $this->request->url();

        $params = [
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => $redirectUrl,
            'openid.realm' => parse_url($redirectUrl, PHP_URL_SCHEME) .'://' . parse_url($redirectUrl, PHP_URL_HOST),
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];

        return self::OPENID_URL . '?' . http_build_query($params, '', '&');
    }

    /**
     * Validates if the request object has
     * required stream attributes.
     *
     * @return bool
     */
    protected function requestIsValid()
    {
        return $this->request->has('openid_assoc_handle')
            && $this->request->has('openid_signed')
            && $this->request->has('openid_sig');
    }
}
