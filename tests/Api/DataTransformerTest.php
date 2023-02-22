<?php

namespace Secavis\Tests\Api;

use PHPUnit\Framework\TestCase;
use Secavis\Api\DataTransformer;
use Secavis\Exception\BadRequestException;

class DataTransformerTest extends TestCase
{
    /**
     * @dataProvider declarantMethodProvider
     */
    public function testDeclarantGetters(string $method, ?string $key, mixed $value, string $input): void
    {
        $this->assertNull((new DataTransformer(static::html()))->$method(1));
        $this->assertNull((new DataTransformer(static::html()))->$method(2));
        $this->assertNull((new DataTransformer(static::html($key)))->$method(1));
        $this->assertNull((new DataTransformer(static::html($key)))->$method(2));
        $this->assertEquals(
            $this->transform($value),
            $this->transform((new DataTransformer(static::html($key, $input)))->$method(1))
        );
        $this->assertEquals(
            $this->transform($value),
            $this->transform((new DataTransformer(static::html($key, null, $input)))->$method(2))
        );
    }

    /**
     * @dataProvider declarationMethodProvider
     */
    public function testDeclarationGetters(string $method, ?string $key, mixed $value, string $input): void
    {
        $this->assertNull((new DataTransformer(static::html()))->$method());
        $this->assertNull((new DataTransformer(static::html($key)))->$method());
        $this->assertEquals(
            $this->transform($value),
            $this->transform((new DataTransformer(static::html($key, $input)))->$method())
        );
    }

    private function transform(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) return $value->format('d/m/Y');
        return $value;
    }

    private static function html(?string $key = null, mixed $value1 = null, mixed $value2 = null): string
    {
        return <<<EOT
        <div id="principal">
            <table>
                <tbody>
                    <tr>
                        <td>$key</td>
                        <td>$value1</td>
                        <td>$value2</td>
                    </tr>
                </tbody>
            </table>
        </div>
        EOT;
    }

    private function declarantMethodProvider(): array
    {
        return [
            ['getNom', 'Nom', 'Doe', 'Doe'],
            ['getNomNaissance', 'Nom de naissance', 'Doe', 'Doe'],
            ['getPrenom', 'Prénom(s)', 'John', 'John'],
            ['getDateNaissance', 'Date de naissance', new \DateTime('1992-01-01'), '01/01/1992'],
            ['getAdresse', 'Adresse déclarée au 1er janvier 2022', '1 rue du test', '1 rue du test'],
            ['getCodePostal', null, '84000', '84000 Avignon'],
            ['getCommune', null, 'Avignon', '84000 Avignon']
        ];
    }

    private function declarationMethodProvider(): array
    {
        return [
            ['getDateRecouvrement', 'Date de mise en recouvrement de l\'avis d\'impôt', new \DateTime('2022-01-01'), '01/01/2022'],
            ['getDateEtablissement', 'Date d\'établissement', new \DateTime('2022-01-12'), '12/01/2022'],
            ['getSituation', 'Situation de famille', 'Célibataire', 'Célibataire'],
            ['getNombreParts', 'Nombre de part(s)', 2.5, '2,5'],
            ['getNombrePersonneCharge', 'Nombre de personne(s) à charge', 3, '3'],
            ['getRevenuBrut', 'Revenu brut global', 22687.68, '22 687 , 68'],
            ['getRevenuImposable', 'Revenu imposable', 0, '0'],
            ['getMontantImpotBrut', 'Impôt sur le revenu net avant corrections', 1368.48, '1 368,48'],
            ['getMontantImpot', 'Montant de l\'impôt', 0, 'Non imposable'],
            ['getRevenusFiscalReference', 'Revenu fiscal de référence', 1532789.28, '1 532 789,28 €']
        ];
    }

}
