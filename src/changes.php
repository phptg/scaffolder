<?php

declare(strict_types=1);

use Vjik\Scaffolder\Change\CopyFile;
use Vjik\Scaffolder\Change\PrepareComposerJson;
use Vjik\Scaffolder\Change\WriteLicense\Bsd3ClauseLicense;
use Vjik\Scaffolder\Change\WriteLicense\WriteLicense;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\PackageProject;

$files = dirname(__DIR__) . '/files';

return [
    new PrepareComposerJson(
        prepareAutoloadDev: false,
        customChange: static function (array &$composerJson, Context $context): void {
            $project = $context->getFact(PackageProject::class);
            $composerJson['support'] = [
                'issues' => "https://github.com/phptg/$project/issues?state=open",
                'chat' => 'https://t.me/predvoditelev_chat',
                'source' => "https://github.com/phptg/$project",
            ];
        }
    ),
    new WriteLicense(
        new Bsd3ClauseLicense('Sergei Predvoditelev'),
    ),
    new CopyFile($files . '/.editorconfig', '.editorconfig'),
];
