<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Tests;

use Ilzrv\LaravelSteamAuth\Exceptions\Authentication\AuthenticationException;
use Ilzrv\LaravelSteamAuth\Exceptions\Validation\InvalidQueryValidationException;
use Ilzrv\LaravelSteamAuth\Exceptions\Validation\InvalidReturnToValidationException;
use Ilzrv\LaravelSteamAuth\SteamAuthenticator;
use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;

final class AuthValidationTest extends TestCase
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
            ['openid_assoc_handle', self::buildHttpQuery('openid_assoc_handle')],
            ['openid_signed', self::buildHttpQuery('openid_signed')],
            ['openid_sig', self::buildHttpQuery('openid_sig')],
            ['openid_return_to', self::buildHttpQuery('openid_return_to')],
            ['openid_claimed_id', self::buildHttpQuery('openid_claimed_id')],
        ];
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws InvalidQueryValidationException
     * @throws AuthenticationException
     * @throws JsonException
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
}
