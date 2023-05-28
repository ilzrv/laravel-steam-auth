<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Tests;

use Ilzrv\LaravelSteamAuth\Exceptions\Authentication\SteamIdNotFoundAuthenticationException;
use Ilzrv\LaravelSteamAuth\Exceptions\Authentication\SteamResponseNotValidAuthenticationException;
use Ilzrv\LaravelSteamAuth\SteamAuthenticator;
use Ilzrv\LaravelSteamAuth\SteamUserDto;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class SteamResponsesTest extends TestCase
{
    public function testSteamInvalidResponse(): void
    {
        $response = $this->createResponseMock(
            <<<TEXT
            ns:http://specs.openid.net/auth/2.0
            is_valid:false\n
            TEXT
        );

        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturn($response);

        $steamAuthenticator = new SteamAuthenticator(
            $this->createUriMock('example.test'),
            $client,
            $this->createMock(RequestFactoryInterface::class),
        );

        $this->expectException(SteamResponseNotValidAuthenticationException::class);

        $steamAuthenticator->auth();
    }

    public function testSteamIdNotFoundResponse(): void
    {
        $response = $this->createResponseMock(
            <<<TEXT
            ns:http://specs.openid.net/auth/2.0
            is_valid:true\n
            TEXT
        );

        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturn($response);

        $steamAuthenticator = new SteamAuthenticator(
            $this->createUriMock('example.test'),
            $client,
            $this->createMock(RequestFactoryInterface::class),
        );

        $this->expectException(SteamIdNotFoundAuthenticationException::class);

        $steamAuthenticator->auth();
    }

    public function testValidSteamResponseWithoutPlayerLevel(): void
    {
        $this->app['config']->set('steam-auth.getting_level', false);

        $response1 = $this->createResponseMock(
            <<<TEXT
            ns:http://specs.openid.net/auth/2.0
            is_valid:true\n
            TEXT
        );

        $response2 = $this->createResponseMock(
            <<<TEXT
            {"response":{"players":[{"steamid":"76561198019153518","communityvisibilitystate":3,"profilestate":1,"personaname":"PUSHASTIY","commentpermission":1,"profileurl":"https://steamcommunity.com/id/ilzrv/","avatar":"https://avatars.steamstatic.com/2c6241be39709151fb4cc4f7d2e55a86b8cff4aa.jpg","avatarmedium":"https://avatars.steamstatic.com/2c6241be39709151fb4cc4f7d2e55a86b8cff4aa_medium.jpg","avatarfull":"https://avatars.steamstatic.com/2c6241be39709151fb4cc4f7d2e55a86b8cff4aa_full.jpg","avatarhash":"2c6241be39709151fb4cc4f7d2e55a86b8cff4aa","lastlogoff":1684170502,"personastate":0,"primaryclanid":"103582791429521408","timecreated":1262328460,"personastateflags":0,"loccountrycode":"VI"}]}}
            TEXT
        );

        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturnOnConsecutiveCalls($response1, $response2);

        $steamAuthenticator = new SteamAuthenticator(
            $this->createUriMockWithOpenidClaimedId('76561198019153518'),
            $client,
            $this->createMock(RequestFactoryInterface::class),
        );

        $steamAuthenticator->auth();

        $this->assertInstanceOf(SteamUserDto::class, $steamAuthenticator->getSteamUser());
    }

    public function testValidSteamResponseWithPlayerLevel(): void
    {
        $this->app['config']->set('steam-auth.getting_level', true);

        $response1 = $this->createResponseMock(
            <<<TEXT
            ns:http://specs.openid.net/auth/2.0
            is_valid:true\n
            TEXT
        );

        $response2 = $this->createResponseMock(
            <<<TEXT
            {"response":{"players":[{"steamid":"76561198019153518","communityvisibilitystate":3,"profilestate":1,"personaname":"PUSHASTIY","commentpermission":1,"profileurl":"https://steamcommunity.com/id/ilzrv/","avatar":"https://avatars.steamstatic.com/2c6241be39709151fb4cc4f7d2e55a86b8cff4aa.jpg","avatarmedium":"https://avatars.steamstatic.com/2c6241be39709151fb4cc4f7d2e55a86b8cff4aa_medium.jpg","avatarfull":"https://avatars.steamstatic.com/2c6241be39709151fb4cc4f7d2e55a86b8cff4aa_full.jpg","avatarhash":"2c6241be39709151fb4cc4f7d2e55a86b8cff4aa","lastlogoff":1684170502,"personastate":0,"primaryclanid":"103582791429521408","timecreated":1262328460,"personastateflags":0,"loccountrycode":"VI"}]}}
            TEXT
        );

        $response3 = $this->createResponseMock(
            <<<TEXT
            {"response":{"player_level":44}}
            TEXT
        );

        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturnOnConsecutiveCalls($response1, $response2, $response3);

        $steamAuthenticator = new SteamAuthenticator(
            $this->createUriMockWithOpenidClaimedId('76561198019153518'),
            $client,
            $this->createMock(RequestFactoryInterface::class),
        );

        $steamAuthenticator->auth();

        $this->assertInstanceOf(SteamUserDto::class, $steamAuthenticator->getSteamUser());
        $this->assertSame(44, $steamAuthenticator->getSteamUser()->getPlayerLevel());
    }

    private function createResponseMock(string $contents): ResponseInterface
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn($contents);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);

        return $response;
    }

    private function createUriMockWithOpenidClaimedId(string $steamId): UriInterface
    {
        $uri = $this->createMock(UriInterface::class);

        $httpQuery = self::buildHttpQuery(null, [
            'openid_return_to' => 'https://example.test/login',
            'openid_claimed_id' => 'https://steamcommunity.com/openid/id/' . $steamId,
        ]);

        $uri->method('getScheme')->willReturn('https');
        $uri->method('getAuthority')->willReturn('example.test');
        $uri->method('getPath')->willReturn('/login');
        $uri->method('getQuery')->willReturn($httpQuery);

        return $uri;
    }
}
