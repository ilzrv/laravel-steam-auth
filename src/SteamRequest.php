<?php

namespace Ilzrv\LaravelSteamAuth;

use Illuminate\Support\Facades\Http;

class SteamRequest
{
    /**
     * @var string
     */
    protected $steamId;

    /**
     * @return string
     */
    public function getSteamId(): string
    {
        return $this->steamId;
    }

    /**
     * @param string $steamId
     */
    public function setSteamId($steamId): void
    {
        $this->steamId = $steamId;
    }

    /**
     * @param $url
     * @return array
     */
    public function get($url)
    {
        $tries = 0;

        while ($tries < config('steam-auth.tries')) {
            $tries++;
            $response = Http::get($this->appendApiKey($url));

            if ($response->ok()) {
                return $response->json();
            }
        }

        return [];
    }

    /**
     * Add Steam API Key to URL
     *
     * @param $url
     * @return string
     */
    protected function appendApiKey($url)
    {
        $apiKeys = config('steam-auth.api_keys');
        $apiKey = $apiKeys[array_rand($apiKeys)];

        return sprintf($url, $apiKey, $this->getSteamId());
    }
}
