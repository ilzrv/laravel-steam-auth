<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Uri;
use Ilzrv\LaravelSteamAuth\Exceptions\AuthenticationException;
use Ilzrv\LaravelSteamAuth\Exceptions\Validation\InvalidQueryValidationException;
use Ilzrv\LaravelSteamAuth\Exceptions\Validation\InvalidReturnToValidationException;
use Ilzrv\LaravelSteamAuth\SteamAuthenticator;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class SteamAuthenticatorTest extends TestCase
{
    /**
     * @dataProvider provideRequiredParams
     *
     * @throws Exception
     * @throws InvalidQueryValidationException
     * @throws InvalidReturnToValidationException
     * @throws AuthenticationException
     * @throws \JsonException
     * @throws ClientExceptionInterface
     */
    public function testAuthValidation(string $param, string $query): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())->method('getQuery')->willReturn($query);

        $steamAuth = new SteamAuthenticator(
            $uri,
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
        );

        $this->expectException(InvalidQueryValidationException::class);
        $this->expectExceptionMessage('The "' . $param . '" parameter is required');

        $steamAuth->auth();
    }

    public static function provideRequiredParams(): array
    {
        return [
            [
                'openid_assoc_handle',
                self::buildHttpQuery('openid_assoc_handle'),
            ],
            [
                'openid_signed',
                self::buildHttpQuery('openid_signed'),
            ],
            [
                'openid_sig',
                self::buildHttpQuery('openid_sig'),
            ],
            [
                'openid_return_to',
                self::buildHttpQuery('openid_return_to'),
            ],
            [
                'openid_claimed_id',
                self::buildHttpQuery('openid_claimed_id'),
            ],
        ];
    }

    private static function buildHttpQuery0(?string $without = null): string
    {
        $params = [
            'openid_assoc_handle' => 'data',
            'openid_signed' => 'data',
            'openid_sig' => 'data',
            'openid_return_to' => 'data',
            'openid_claimed_id' => 'data',
        ];

        unset($params[$without]);

        return http_build_query($params);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws InvalidQueryValidationException
     * @throws AuthenticationException
     * @throws \JsonException
     */
    public function testValidateReturnToUrl(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->exactly(2))->method('getQuery')->willReturn(self::buildHttpQuery());

        $steamAuth = new SteamAuthenticator(
            $uri,
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
        );

        $this->expectException(InvalidReturnToValidationException::class);
        $this->expectExceptionMessage('openid_return_to does not match redirect url');

        $steamAuth->auth();
    }




//    public function testAuth()//: void
//    {
//        $uri = 'https://skgr-general.test/login?openid.assoc_handle=1234567890&openid.claimed_id=https%3A%2F%2Fsteamcommunity.com%2Fopenid%2Fid%2F76561198019153518&openid.identity=https%3A%2F%2Fsteamcommunity.com%2Fopenid%2Fid%2F76561198019153518&openid.mode=id_res&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.op_endpoint=https%3A%2F%2Fsteamcommunity.com%2Fopenid%2Flogin&openid.response_nonce=2023-05-21T18%3A07%3A30Z8X7lqO7qjMaYy0x2dh8E6%2Fnb3Wk%3D&openid.return_to=https%3A%2F%2Fskgr-general.test%2Flogin&openid.sig=c3%2BQpiCj%2BU1v9Bnjhp60s7ywFI4%3D&openid.signed=signed%2Cop_endpoint%2Cclaimed_id%2Cidentity%2Creturn_to%2Cresponse_nonce%2Cassoc_handle';
//
////        $req = new Request(new Uri($uri));
////        $req->getSome();
////        exit();
//
//        $steamAuth = new SteamAuthenticator(
//            new Uri($uri),
//            new Client(),
//            new HttpFactory()
//        );
//
//        try {
//            $steamAuth->auth();
//        } catch (\Exception) {
//            return $steamAuth->buildAuthUrl();
//        }
//
//        $steamAuth->getSteamUser();
//    }
}
