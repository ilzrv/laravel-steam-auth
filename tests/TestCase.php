<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
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
}

