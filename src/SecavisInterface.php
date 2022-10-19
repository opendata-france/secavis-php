<?php

namespace Secavis;

use Secavis\Entity\Declaration;

interface SecavisInterface
{
    public static function get(string $referenceAvis, string $numeroFiscal): ?Declaration;
}
