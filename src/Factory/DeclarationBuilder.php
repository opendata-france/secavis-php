<?php

namespace Secavis\Factory;

use Secavis\Response\Declarant;
use Secavis\Response\Declaration;
use Secavis\Request\IdentifiantFiscal;
use Secavis\Request\ReferenceAvis;

class DeclarationBuilder
{
    use HtmlParserTrait;

    private DeclarantBuilder $declarantBuilder;

    public function __construct(private string $html)
    {
        $this->declarantBuilder = new DeclarantBuilder($html);
    }

    public function buildDeclaration(IdentifiantFiscal $identifiantFiscal, ReferenceAvis $referenceAvis): ?Declaration
    {
        return new Declaration(
            annee: $referenceAvis->getAnneeReference(),
            dateRecouvrement: $this->getDateRecouvrement(),
            dateEtablissement: $this->getDateEtablissement(),
            parts: $this->getNombreParts(),
            situationFamille: $this->getSituation(),
            personnesCharge: $this->getNombrePersonneCharge(),
            revenuBrut: $this->getRevenuBrut(),
            revenuImposable: $this->getRevenuImposable(),
            montantImpotBrut: $this->getMontantImpotBrut(),
            montantImpot: $this->getMontantImpot(),
            revenusFiscalReference: $this->getRevenusFiscalReference(),
            declarants: \array_filter(
                \array_map(fn(int $i): Declarant => $this->declarantBuilder->build($i), [1, 2]),
                fn(Declarant $item): bool => $item->isValide()
            )
        );
    }

    private function getDateRecouvrement(): ?\DateTimeInterface
    {
        return ($item = $this->getBy('Date de mise en recouvrement de l\'avis d\'impôt'))->count()
            ? $this->toDate($item->text())
            : null;
    }

    private function getDateEtablissement(): ?\DateTimeInterface
    {
        return ($item = $this->getBy('Date d\'établissement'))->count() ? $this->toDate($item->text()) : null;
    }

    private function getSituation(): ?string
    {
        return ($item = $this->getBy('Situation de famille'))->count() ? $this->toString($item->text()) : null;
    }

    private function getNombreParts(): ?float
    {
        return ($item = $this->getBy('Nombre de part(s)'))->count() ? $this->toFloat($item->text()) : null;
    }

    private function getNombrePersonneCharge(): ?float
    {
        return ($item = $this->getBy('Nombre de personne(s) à charge'))->count() ? $this->toFloat($item->text()) : null;
    }

    private function getRevenuBrut(): ?float
    {
        return ($item = $this->getBy('Revenu brut global'))->count() ? $this->toEuro($item->text()) : null;
    }

    private function getRevenuImposable(): ?float
    {
        return ($item = $this->getBy('Revenu imposable'))->count() ? $this->toEuro($item->text()) : null;
    }

    private function getMontantImpotBrut(): ?float
    {
        return ($item = $this->getBy('Impôt sur le revenu net avant corrections'))->count() ? $this->toEuro($item->text()) : null;
    }

    private function getMontantImpot(): ?float
    {
        return ($item = $this->getBy('Montant de l\'impôt'))->count() ? $this->toEuro($item->text()) : null;
    }

    private function getRevenusFiscalReference(): ?float
    {
        return ($item = $this->getBy('Revenu fiscal de référence'))->count() ? $this->toEuro($item->text()) : null;
    }

}
