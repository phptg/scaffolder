<?php

declare(strict_types=1);

use Phptg\Scaffolder\Fact;

return [
    Fact\UseComposerDependencyAnalyser::class,
    Fact\UseInfection::class,
    Fact\UsePhpStan::class,
    Fact\UsePhpUnit::class,
    Fact\UsePsalm::class,
];
