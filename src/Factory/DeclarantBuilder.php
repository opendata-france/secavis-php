<?php

namespace Secavis\Factory;

use Secavis\Response\Declarant;
use Symfony\Component\DomCrawler\Crawler;

class DeclarantBuilder
{
    use HtmlParserTrait;

    public function __construct(private string $html)
    {
    }

    public function build(int $i): Declarant
    {
        return new Declarant(
            nom: $this->getNom($i),
            nomNaissance: $this->getNomNaissance($i),
            prenom: $this->getPrenom($i),
            dateNaissance: $this->getDateNaissance($i),
            adresse: $this->getAdresse($i),
            codePostal: $this->getCodePostal($i),
            commune: $this->getCommune($i),
        );
    }

    public function getNom(int $i): ?string
    {
        return $this->key($i) && ($item = $this->getBy('Nom', $this->key($i)))->count()
            ? $this->toString($item->text())
            : null;
    }

    public function getNomNaissance(int $i): ?string
    {
        return $this->key($i) && ($item = $this->getBy('Nom de naissance', $this->key($i)))->count()
            ? $this->toString($item->text())
            : null;
    }

    public function getPrenom(int $i): ?string
    {
        return $this->key($i) && ($item = $this->getBy('Prénom(s)', $this->key($i)))->count()
            ? $this->toString($item->text())
            : null;
    }

    public function getDateNaissance(int $i): ?\DateTimeInterface
    {
        return $this->key($i) && ($item = $this->getBy('Date de naissance', $this->key($i)))->count()
            ? $this->toDate($item->text())
            : null;
    }

    public function getAdresse(int $i): ?string
    {
        if (null === $i = $this->key($i)) {
            return null;
        }

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
        if (null === $i = $this->key($i)) {
            return null;
        }

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
        if (null === $i = $this->key($i)) {
            return null;
        }

        return ($item = $this->table()
            ->filter('tbody > tr')
            ->reduce(function (Crawler $item) use ($i) {
                return ($cell = $item->filter('td')->eq($i))->count() && \preg_match('/^\d{5,5}(\w+|\s+)+$/', \trim($cell->text()));
            })
            ->filter('td')
            ->eq($i)
        )->count() ? $this->toString(\substr($item->text(), 6)) : null;
    }
}
