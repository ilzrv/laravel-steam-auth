<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Exceptions\Validation;

final class InvalidReturnToValidationException extends ValidationException
{
    public static function invalidReturnTo(): self
    {
        return new InvalidReturnToValidationException('openid_return_to does not match redirect url');
    }
}
