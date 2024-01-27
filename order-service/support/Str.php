<?php

declare(strict_types=1);

namespace Support;

final class Str
{
    public static function camelCaseToSnakeCase(string $value): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }
}
