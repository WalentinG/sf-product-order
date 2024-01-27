<?php

declare(strict_types=1);

namespace Support;

final readonly class Uuid
{

    public function __construct(
        public string $value,
    ) {
    }

    public static function v4(): self
    {
        $uuid = random_bytes(16);
        $uuid[6] = $uuid[6] & "\x0F" | "\x4F";
        $uuid[8] = $uuid[8] & "\x3F" | "\x80";
        $uuid = bin2hex($uuid);

        return new self(substr($uuid, 0, 8) .
            '-' .
            substr($uuid, 8, 4) . '-' .
            substr($uuid, 12, 4) . '-' .
            substr($uuid, 16, 4) . '-' .
            substr($uuid, 20, 12));
    }
}
