<?php

declare(strict_types=1);

use Vjik\Scaffolder\Change\NormalizeComposerJson;
use Vjik\Scaffolder\Change\WriteLicense\Bsd3ClauseLicense;
use Vjik\Scaffolder\Change\WriteLicense\WriteLicense;

return [
    new WriteLicense(
        new Bsd3ClauseLicense('Sergei Predvoditelev'),
    ),
    new NormalizeComposerJson(),
];
