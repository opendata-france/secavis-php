<?php

namespace Secavis\Api;

use Secavis\Exception\BadRequestException;
use Symfony\Component\DomCrawler\Crawler;

class DataTransformer
{
    public function __construct(public readonly string $html) {}

    public function getNom(int $i): ?string
    {
        return ($item = $this->getBy('Nom', $this->key($i)))->count() ? $this->toString($item->text()) : null;
    }

    public function getNomNaissance(int $i): ?string
    {
        return ($item = $this->getBy('Nom de naissance', $this->key($i)))->count() ? $this->toString($item->text()) : null;
    }

    public function getPrenom(int $i): ?string
    {
        return ($item = $this->getBy('Prénom(s)', $this->key($i)))->count() ? $this->toString($item->text()) : null;
    }

    public function getDateNaissance(int $i): ?\DateTimeInterface
    {
        return ($item = $this->getBy('Date de naissance', $this->key($i)))->count() ? $this->toDate($item->text()) : null;
    }

    public function getAdresse(int $i): ?string
    {
        $i = $this->key($i);

        return ($item = $this->table()
            ->filter('tbody > tr')
            ->reduce(function (Crawler $item) {
                return ($cell = $item->filter('td')->eq(0))->count() && \str_contains(\trim($cell->text()), 'Adresse déclarée');
            })
            ->filter('td')
            ->eq($i)
        )->count() ? $this->toString($item->text()) : null;
    }

    public function getCodePostal(int $i): ?string
    {
        $i = $this->key($i);

        return ($item = $this->table()
            ->filter('tbody > tr')
            ->reduce(function (Crawler $item) use ($i) {
                return ($cell = $item->filter('td')->eq($i))->count() && \preg_match('/^\d{5,5}(\w+|\s+)+$/', \trim($cell->text()));
            })
            ->filter('td')
            ->eq($i)
        )->count() ? $this->toString(\substr($item->text(), 0, 5)) : null;
    }

    public function getCommune(int $i): ?string
    {
        $i = $this->key($i);

        return ($item = $this->table()
            ->filter('tbody > tr')
            ->reduce(function (Crawler $item) use ($i) {
                return ($cell = $item->filter('td')->eq($i))->count() && \preg_match('/^\d{5,5}(\w+|\s+)+$/', \trim($cell->text()));
            })
            ->filter('td')
            ->eq($i)
        )->count() ? $this->toString(\substr($item->text(), 6)) : null;
    }

    public function getDateRecouvrement(): ?\DateTimeInterface
    {
        return ($item = $this->getBy('Date de mise en recouvrement de l\'avis d\'impôt'))->count()
            ? $this->toDate($item->text())
            : null;
    }

    public function getDateEtablissement(): ?\DateTimeInterface
    {
        return ($item = $this->getBy('Date d\'établissement'))->count() ? $this->toDate($item->text()) : null;
    }

    public function getSituation(): ?string
    {
        return ($item = $this->getBy('Situation de famille'))->count() ? $this->toString($item->text()) : null;
    }

    public function getNombreParts(): ?float
    {
        return ($item = $this->getBy('Nombre de part(s)'))->count() ? $this->toFloat($item->text()) : null;
    }

    public function getNombrePersonneCharge(): ?float
    {
        return ($item = $this->getBy('Nombre de personne(s) à charge'))->count() ? $this->toFloat($item->text()) : null;
    }

    public function getRevenuBrut(): ?float
    {
        return ($item = $this->getBy('Revenu brut global'))->count() ? $this->toEuro($item->text()) : null;
    }

    public function getRevenuImposable(): ?float
    {
        return ($item = $this->getBy('Revenu imposable'))->count() ? $this->toEuro($item->text()) : null;
    }

    public function getMontantImpotBrut(): ?float
    {
        return ($item = $this->getBy('Impôt sur le revenu net avant corrections'))->count() ? $this->toEuro($item->text()) : null;
    }

    public function getMontantImpot(): ?float
    {
        return ($item = $this->getBy('Montant de l\'impôt'))->count() ? $this->toEuro($item->text()) : null;
    }

    public function getRevenusFiscalReference(): ?float
    {
        return ($item = $this->getBy('Revenu fiscal de référence'))->count() ? $this->toEuro($item->text()) : null;
    }

    private function toString(?string $value): ?string
    {
        return empty($value) ? null : \trim($value);
    }

    private function toFloat(?string $value): ?float
    {
        $value = \str_replace([',', ' '], ['.', ''], $value);
        return \is_numeric($value) ? (float) $value : null;
    }

    private function toDate(?string $value): ?\DateTimeInterface
    {
        return ($date = \DateTime::createFromFormat('d/m/Y', $value)) ? $date : null;
    }

    private function toEuro(?string $value): ?float
    {
        if ($value === 'Non imposable') return (float) 0;
        \preg_match('/(\d+(\.\d+)?)/', \str_replace([',', ' '], ['.', ''], $value), $matches);
        $value = $matches[0] ?? null;
    
        return $value ? (float) $value : null;
    }

    private function getBy(string $key, ?int $i = 1): Crawler
    {
        return $this->table()
            ->filter('tbody > tr')
            ->reduce(function (Crawler $item) use ($key) {
                return ($cell = $item->filter('td')->eq(0))->count() && \trim($cell->text()) === $key;
            })
            ->filter('td')
            ->eq($i);
    }

    private function table(): Crawler
    {
        return (new Crawler(\str_replace(\html_entity_decode('&nbsp;'), ' ', $this->html)))->filter('#principal > table');
    }

    private function key(int $i): int
    {
        if (!$i || $i > 2) throw new BadRequestException();
        return $i;
    }
}
