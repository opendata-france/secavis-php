# @opendata-france : Secavis

Service PHP par dessus le service de vérification du justificatif et de l'avis d'impôt sur le revenu.

## Installation

```
composer require opendata-france/secavis-php
```
## Utilisation

```
<?php

use Secavis\Secavis;

$declaration = Secavis::get('REFERENCE_AVIS', 'IDENTIFIANT_FISCAL');
```
