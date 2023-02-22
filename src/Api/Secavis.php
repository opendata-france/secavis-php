<?php

namespace Secavis\Api;

use Secavis\Exception\DeclarationNotFoundException;
use Secavis\Exception\ServiceUnavailableException;
use Secavis\Request\IdentifiantFiscal;
use Secavis\Request\ReferenceAvis;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\DomCrawler\Crawler;

class Secavis
{
    private const BASE_URL = 'https://cfsmsp.impots.gouv.fr';

    /**
     * @throws ServiceUnavailableException
     */
    public static function get(IdentifiantFiscal $identifiantFiscal, ReferenceAvis $referenceAvis): null|string
    {
        /** @var ResponseInterface */
        $preRespone = static::preRequest();

        if ($preRespone->getStatusCode() !== 200) {
            throw new ServiceUnavailableException($preRespone->getContent());
        }
        $html = $preRespone->getContent();
        $viewState = static::parsePreRequest($html);

        if (empty($viewState)) {
            throw new \RuntimeException('Erreur lors de la récupération de la valeur ViewsState.');
        }

        /** @var ResponseInterface */
        $response = static::request($viewState, $identifiantFiscal, $referenceAvis);

        if ($response->getStatusCode() !== 200) {
            throw new ServiceUnavailableException($response->getContent());
        }

        $html = $response->getContent();

        if ((new Crawler($html))->filter('div[class="titre_affiche_avis"]')->count() === 0) {
            throw new DeclarationNotFoundException($html);
        }
        return $html;
    }

    private static function preRequest(): ResponseInterface
    {
        $client = HttpClient::create();
        return $client->request('GET', self::BASE_URL . '/secavis/');
    }

    private static function parsePreRequest(string $html): ?string
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter('input[name="javax.faces.ViewState"]');
        return $nodes->count() > 0 ? $nodes->first()->attr('value') : null;
    }

    private static function request(null|string $viewState, IdentifiantFiscal $identifiantFiscal, ReferenceAvis $referenceAvis): ResponseInterface
    {
        $formFields = self::mapFormData();
        $formFields['j_id_7:spi'] = $identifiantFiscal->value;
        $formFields['j_id_7:num_facture'] = $referenceAvis->value;
        $formFields['javax.faces.ViewState'] = $viewState;

        $formData = new FormDataPart($formFields);

        $client = HttpClient::create();

        return $client->request('POST',  self::BASE_URL . '/secavis/faces/commun/index.jsf', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);
    }

    private static function mapFormData(): array
    {
        return [
            'j_id_7:spi' => null,
            'j_id_7:num_facture' => null,
            'j_id_7:j_id_l' => 'Valider',
            'j_id_7_SUBMIT' => '1',
            'javax.faces.ViewState' => null
        ];
    }

}
