<?php

namespace Secavis\Response;

final class Declaration
{
    public function __construct(
        public readonly ?string $annee,
        public ?\DateTimeInterface $dateRecouvrement,
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
        public readonly array $declarants = []
    ) {}

    public function getDeclarant(int $key): ?Declarant
    {
        return $this->declarants[$key] ?? null;
    }

    public function isValide(): bool
    {
        foreach (\get_object_vars($this) as $value) {
            if (null === $value) {
                return false;
            }
        }
        return \array_key_exists(0, $this->declarants) && $this->declarants[0]->isValide();
    }
}
