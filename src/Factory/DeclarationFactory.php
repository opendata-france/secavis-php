<?php

namespace Secavis\Factory;

use Secavis\Api\Secavis;
use Secavis\Response\Declaration;
use Secavis\Request\IdentifiantFiscal;
use Secavis\Request\ReferenceAvis;

class DeclarationFactory
{
    private Secavis $api;

    public function __construct()
    {
        $this->api = new Secavis;
    }

    public function from(IdentifiantFiscal $identifiantFiscal, ReferenceAvis $referenceAvis): Declaration
    {
        $html = $this->api::get($identifiantFiscal, $referenceAvis);
        $builder = new DeclarationBuilder($html);
        return $builder->buildDeclaration($identifiantFiscal, $referenceAvis);
    }
}
