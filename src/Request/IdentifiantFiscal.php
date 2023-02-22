<?php

namespace Secavis\Request;

class IdentifiantFiscal
{
    final const PATTERN = '/^[0-9]{13,13}$/';

    public function __construct(public readonly string $value)
    {
        if (!self::match($value)) {
            throw new \InvalidArgumentException();
        }
    }

    public static function match(string $value): bool
    {
        return \preg_match(self::PATTERN, $value);
    }

    public static function tryFrom(string $value): ?static
    {
        return self::match($value) ? new Static($value): null;
    }

    public static function from(string $value): static
    {
        return new Static($value);
    }
}
