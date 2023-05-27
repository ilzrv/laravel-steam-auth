<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Tests;

use Ilzrv\LaravelSteamAuth\Exceptions\AuthenticationException;
use Ilzrv\LaravelSteamAuth\SteamAuthenticator;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class SteamResponsesTest extends TestCase
{
    public function testSteamInvalidResponse(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->expects(self::atMost(1))->method('getContents')->willReturn(
            <<<TEXT
            ns:http://specs.openid.net/auth/2.0
            is_valid:false\n
            TEXT
        );

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::atMost(1))->method('getBody')->willReturn($body);

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('sendRequest')->willReturn($response);

        $steamAuth = new SteamAuthenticator(
            $this->createUriMock(),
            $client,
            $this->createMock(RequestFactoryInterface::class),
        );

        $this->expectException(AuthenticationException::class);
        $steamAuth->auth();
    }

//    public function testSteamInvalidResponse(): void
//    {
//
//    }

    private function createUriMock(): UriInterface
    {
        $uri = $this->createMock(UriInterface::class);

        $uri->method('getScheme')->willReturn('https');
        $uri->method('getAuthority')->willReturn('example.test');
        $uri->method('getPath')->willReturn('/login');
        $uri->method('getQuery')->willReturn(self::buildHttpQuery(null, ['openid_return_to' => 'https://example.test/login']));

        return $uri;
    }
}
