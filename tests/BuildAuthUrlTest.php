<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Tests;

use Ilzrv\LaravelSteamAuth\SteamAuthenticator;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;

final class BuildAuthUrlTest extends TestCase
{
    public function testAuthUrlWithoutRedirectUrl(): void
    {
        $this->app['config']->set('steam-auth.redirect_url', null);

        $steamAuthenticator = new SteamAuthenticator(
            $this->createUriMock('example.test'),
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
        );

        $uri = 'https://steamcommunity.com/openid/login?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.return_to=https%3A%2F%2Fexample.test%2Flogin&openid.realm=https%3A%2F%2Fexample.test&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select';

        $this->assertSame(
            $uri,
            $steamAuthenticator->buildAuthUrl()
        );
    }

    public function testAuthUrlWithRedirectUrl(): void
    {
        $this->app['config']->set('steam-auth.redirect_url', 'https://used-example.test/login');

        $steamAuthenticator = new SteamAuthenticator(
            $this->createUriMock('unused-example.test'),
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
        );

        $uri = 'https://steamcommunity.com/openid/login?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.return_to=https%3A%2F%2Fused-example.test%2Flogin&openid.realm=https%3A%2F%2Fused-example.test&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select';

        $this->assertSame(
            $uri,
            $steamAuthenticator->buildAuthUrl()
        );
    }
}
