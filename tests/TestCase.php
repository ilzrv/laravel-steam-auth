<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Tests;

use Ilzrv\LaravelSteamAuth\ServiceProvider;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\UriInterface;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected static function buildHttpQuery(
        string $without = null,
        array $replace = [],
    ): string {
        $params = [
            'openid_assoc_handle' => 'data',
            'openid_signed' => 'data',
            'openid_sig' => 'data',
            'openid_return_to' => 'data',
            'openid_claimed_id' => 'data',
        ];

        $params = array_replace($params, $replace);

        unset($params[$without]);

        return http_build_query($params);
    }

    /**
     * @throws Exception
     */
    protected function createUriMock(
        string $authority,
    ): UriInterface {
        $uri = $this->createMock(UriInterface::class);

        $uri->method('getScheme')->willReturn('https');
        $uri->method('getAuthority')->willReturn($authority);
        $uri->method('getPath')->willReturn('/login');
        $uri->method('getQuery')->willReturn(
            self::buildHttpQuery(null, ['openid_return_to' => "https://$authority/login"])
        );

        return $uri;
    }
}

