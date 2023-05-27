<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth;

use Ilzrv\LaravelSteamAuth\Exceptions\AuthenticationException;
use Ilzrv\LaravelSteamAuth\Exceptions\Validation\InvalidQueryValidationException;
use Ilzrv\LaravelSteamAuth\Exceptions\Validation\InvalidReturnToValidationException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;

final class SteamAuthenticator
{
    private const OPENID_URL = 'https://steamcommunity.com/openid/login';
    private const STEAM_DATA_URL = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s';
    private const STEAM_LEVEL_URL = 'https://api.steampowered.com/IPlayerService/GetSteamLevel/v1/?key=%s&format=json&steamid=%s';

    private ?SteamUserDto $steamUserDto;

    public function __construct(
        private readonly UriInterface $uri,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidQueryValidationException
     * @throws InvalidReturnToValidationException
     * @throws AuthenticationException
     * @throws \JsonException
     */
    public function auth(): void
    {
        $this->validateRequest();
        $this->validateReturnToUrl();

        $response = $this->httpClient->sendRequest(
            $this->requestFactory->createRequest('GET', self::OPENID_URL . '?' . $this->buildOpenIdRequestQuery()),
        );

        $contents = $response->getBody()->getContents();

        if (preg_match("#is_valid\s*:\s*true#i", $contents) !== 1) {
            throw new AuthenticationException();
        }

        $query = $this->parseUriQueryString();

        preg_match(
            "#^https?://steamcommunity.com/openid/id/([0-9]{17,25})#",
            $query['openid_claimed_id'],
            $matches
        );

        if (!isset($matches[1]) || !is_numeric($matches[1])) {
            throw new \Exception();
        }

        $this->loadSteamUser($matches[1]);
    }

    private function loadSteamUser(string $steamId)
    {
        $steamDataResponse = $this->httpClient->sendRequest(
            $this->requestFactory->createRequest('GET', sprintf(self::STEAM_DATA_URL, $this->getApiKey(), $steamId)),
        );

        $steamData = json_decode(
            $steamDataResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        )['response']['players'][0];

        if (config('steam-auth.getting_level')) {
            $userLevelResponse = $this->httpClient->sendRequest(
                $this->requestFactory->createRequest(
                    'GET',
                    sprintf(self::STEAM_LEVEL_URL, $this->getApiKey(), $steamId)
                )
            );

            $steamData = array_merge(
                $steamData,
                json_decode($userLevelResponse->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)['response']
            );
        }

        $this->steamUserDto = SteamUserDto::create($steamData);
    }

    private function getApiKey()
    {
        $apiKeys = config('steam-auth.api_keys');

        return $apiKeys[array_rand($apiKeys)];
    }

    private function buildOpenIdRequestQuery(): string
    {
        $query = $this->parseUriQueryString();

        $params = [
            'openid.assoc_handle' => $query['openid_assoc_handle'],
            'openid.signed' => $query['openid_signed'],
            'openid.sig' => $query['openid_sig'],
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'check_authentication',
        ];

        $signed = explode(',', $query['openid_signed']);

        foreach ($signed as $item) {
            $params['openid.' . $item] = $query['openid_' . str_replace('.', '_', $item)] ?? null;
        }

        return http_build_query($params);
    }

    public function getSteamUser(): ?SteamUserDto
    {
        return $this->steamUserDto;
    }

    /**
     * @throws InvalidQueryValidationException
     */
    private function validateRequest(): void
    {
        $query = $this->parseUriQueryString();

        $params = [
            'openid_assoc_handle',
            'openid_signed',
            'openid_sig',
            'openid_return_to',
            'openid_claimed_id',
        ];

        foreach ($params as $param) {
            if (!isset($query[$param])) {
                throw InvalidQueryValidationException::invalidQuery($param);
            }
        }
    }

    /**
     * @throws InvalidReturnToValidationException
     */
    private function validateReturnToUrl(): void
    {
        $query = $this->parseUriQueryString();

        if ($this->buildRedirectUrl() !== $query['openid_return_to']) {
            throw InvalidReturnToValidationException::invalidReturnTo();
        }
    }

    private function parseUriQueryString(): array
    {
        parse_str($this->uri->getQuery(), $result);

        return $result;
    }

    public function buildAuthUrl(): string
    {
        $redirectUrl = $this->buildRedirectUrl();

        $params = [
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => $redirectUrl,
            'openid.realm' => parse_url($redirectUrl, PHP_URL_SCHEME) . '://' . parse_url($redirectUrl, PHP_URL_HOST),
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];

        return self::OPENID_URL . '?' . http_build_query($params, '', '&');
    }

    private function buildRedirectUrl(): string
    {
        return config('steam-auth.redirect_url')
            ? url(config('steam-auth.redirect_url'))
            : $this->uri->getScheme() . '://' . $this->uri->getAuthority() . $this->uri->getPath();
    }
}
