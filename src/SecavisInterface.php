<?php

namespace Secavis;

use Secavis\Response\Declaration;

interface SecavisInterface
{
    /**
     * @param string Identifiant fiscal
     * @param string Référence de l'avis d'imposition
     * 
     * @throws \InvalidArgumentException
     * @throws Secavis\Exception\ServiceUnavailableException
     */
    public static function get(string $identifiantFiscal, string $referenceAvis): Declaration;
}
