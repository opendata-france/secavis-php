<?php

namespace Secavis\Response;

final class Declarant
{
    public function __construct(
        public readonly ?string $nom,
        public readonly ?string $nomNaissance,
        public readonly ?string $prenom,
        public readonly ?\DateTimeInterface $dateNaissance,
        public readonly ?string $adresse,
        public readonly ?string $codePostal,
        public readonly ?string $commune
    ) {}

    public function isValide(): bool
    {
        return null !== $this->nom;
    }
}
