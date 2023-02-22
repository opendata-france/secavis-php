<?php

namespace Secavis;

use Secavis\Factory\DeclarationFactory;
use Secavis\Response\Declaration;
use Secavis\Request\IdentifiantFiscal;
use Secavis\Request\ReferenceAvis;

class Secavis implements SecavisInterface
{
    /**
     * @inheritdoc
     */
    public static function get(string $identifiantFiscal, string $referenceAvis): Declaration
    {
        return (new DeclarationFactory)->from(
            IdentifiantFiscal::from($identifiantFiscal),
            ReferenceAvis::from($referenceAvis)
        );
    }
}
