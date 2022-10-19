<?php

namespace Secavis\Entity;

use Secavis\Api\Secavis;
use Secavis\Api\DataTransformer;
use Secavis\Exception\BadRequestException;

final class Declaration
{
    public function __construct(
        public readonly ?string $annee,
        public \DateTimeInterface $dateRecouvrement,
        public readonly ?\DateTimeInterface $dateEtablissement,
        public readonly ?float $parts,
        public readonly ?string $situationFamille,
        public readonly ?int $personnesCharge,
        public readonly ?float $revenuBrut,
        public readonly ?float $revenuImposable,
        public readonly ?float $montantImpotBrut,
        public readonly ?float $montantImpot,
        public readonly ?float $revenusFiscalReference,
        /** @var array|Declarant[] */
        public readonly array $declarants
    ) {}

    public static function create(string $numeroFiscal, string $referenceAvis): static
    {
        if (!preg_match('/^\w{13,14}$/', $referenceAvis)) {
            throw new BadRequestException();
        }
        if (!preg_match('/^\d{13,13}$/', $numeroFiscal)) {
            throw new BadRequestException();
        }
        $html = Secavis::get($numeroFiscal, $referenceAvis);
        $transformer = new DataTransformer($html);

        return new static(
            static::getAnneeFromReferenceAvis($referenceAvis),
            $transformer->getDateRecouvrement(),
            $transformer->getDateEtablissement(),
            $transformer->getNombreParts(),
            $transformer->getSituation(),
            $transformer->getNombrePersonneCharge(),
            $transformer->getRevenuBrut(),
            $transformer->getRevenuImposable(),
            $transformer->getMontantImpotBrut(),
            $transformer->getMontantImpot(),
            $transformer->getRevenusFiscalReference(),
            \array_filter(\array_map(function(int $i) use ($transformer) {
                return new Declarant(
                    $transformer->getNom($i),
                    $transformer->getNomNaissance($i),
                    $transformer->getPrenom($i),
                    $transformer->getDateNaissance($i),
                    $transformer->getAdresse($i),
                    $transformer->getCodePostal($i),
                    $transformer->getCommune($i),
                );
            }, [1, 2]), fn(Declarant $item): bool => !empty($item->nom))
        );
    }

    public static function getAnneeFromReferenceAvis(string $referenceAvis): string
    {
        return (string) \substr((new \DateTime())->format('Y'), 0, 2) . \substr($referenceAvis, 0, 2);
    }

    public function declarant(int $index): ?Declarant
    {
        return $this->declarants[$index] ?? null;
    }
}
