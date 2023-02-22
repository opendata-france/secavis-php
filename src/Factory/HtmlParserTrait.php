<?php

namespace Secavis\Factory;

use Symfony\Component\DomCrawler\Crawler;

trait HtmlParserTrait
{
    protected function toString(?string $value): ?string
    {
        return empty($value) ? null : \trim($value);
    }

    protected function toFloat(?string $value): ?float
    {
        $value = \str_replace([',', ' '], ['.', ''], $value);
        return \is_numeric($value) ? (float) $value : null;
    }

    protected function toDate(?string $value): ?\DateTimeInterface
    {
        return ($date = \DateTime::createFromFormat('d/m/Y', $value)) ? $date : null;
    }

    protected function toEuro(?string $value): ?float
    {
        if ($value === 'Non imposable') return (float) 0;
        \preg_match('/(\d+(\.\d+)?)/', \str_replace([',', ' '], ['.', ''], $value), $matches);
        $value = $matches[0] ?? null;

        return $value ? (float) $value : null;
    }

    protected function getBy(string $key, ?int $i = 1): Crawler
    {
        return $this->table()
            ->filter('tbody > tr')
            ->reduce(function (Crawler $item) use ($key) {
                return ($cell = $item->filter('td')->eq(0))->count() && \trim($cell->text()) === $key;
            })
            ->filter('td')
            ->eq($i);
    }

    protected function table(): Crawler
    {
        return (new Crawler(\str_replace(\html_entity_decode('&nbsp;'), ' ', $this->html)))->filter('#principal > table');
    }

    protected function key(int $i): ?int
    {
        return $i && $i <= 2 ? $i : null;
    }
}
