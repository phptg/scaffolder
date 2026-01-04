<?php

declare(strict_types=1);

return [
    'title' => 'PHPTG Scaffolder',
    'disable' => [
        'docs-internals',
        'tests-directory',
    ],
    'prepare-composer-autoload-dev' => false,
    'use-phpunit' => false,
    'use-psalm' => false,
    'use-phpstan' => true,
    'use-infection' => false,
];
