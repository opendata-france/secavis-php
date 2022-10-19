<?php

namespace Secavis;

use Secavis\Entity\Declaration;

class Secavis implements SecavisInterface
{
    /**
     * @inheritdoc
     */
    public static function get(string $referenceAvis, string $numeroFiscal): ?Declaration
    {
        return Declaration::create($numeroFiscal, $referenceAvis);
    }
}
