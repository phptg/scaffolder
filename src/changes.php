<?php

declare(strict_types=1);

use Vjik\Scaffolder\Change\CopyFile;
use Vjik\Scaffolder\Change\NormalizeComposerJson;
use Vjik\Scaffolder\Change\WriteLicense\Bsd3ClauseLicense;
use Vjik\Scaffolder\Change\WriteLicense\WriteLicense;

$files = dirname(__DIR__) . '/files';

return [
    new WriteLicense(
        new Bsd3ClauseLicense('Sergei Predvoditelev'),
    ),
    new CopyFile($files . '/.editorconfig', '.editorconfig'),
    new NormalizeComposerJson(),
];
