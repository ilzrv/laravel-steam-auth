<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth\Exceptions\Validation;

final class InvalidQueryValidationException extends ValidationException
{
    public static function invalidQuery(string $param): self
    {
        return new InvalidQueryValidationException('The "' . $param . '" parameter is required');
    }
}
