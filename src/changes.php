<?php

declare(strict_types=1);

use Phptg\Scaffolder\Change\PrepareReadme;
use Phptg\Scaffolder\Change\PrepareRectorConfiguration;
use Vjik\Scaffolder\Change\CopyFile;
use Vjik\Scaffolder\Change\PrepareComposerJson;
use Vjik\Scaffolder\Change\WriteLicense\Bsd3ClauseLicense;
use Vjik\Scaffolder\Change\WriteLicense\WriteLicense;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\PackageProject;

$files = dirname(__DIR__) . '/files';

return [
    new PrepareComposerJson(
        customChange: static function (array &$new, Context $context): void {
            $project = $context->getFact(PackageProject::class);
            $new['support'] = [
                'issues' => "https://github.com/phptg/$project/issues?state=open",
                'chat' => 'https://t.me/predvoditelev_chat',
                'source' => "https://github.com/phptg/$project",
            ];

            // Rector
            $new['require-dev']['rector/rector'] ??= '^2.3.0';
            $new['scripts']['rector'] = 'rector';
        }
    ),
    new WriteLicense(
        new Bsd3ClauseLicense('Sergei Predvoditelev'),
    ),
    new CopyFile($files . '/.editorconfig', '.editorconfig'),
    new PrepareRectorConfiguration(),
    'readme' => [
        new PrepareReadme(),
        new CopyFile($files . '/logo.png', 'logo.png'),
    ],
    'docs-internals' => new CopyFile($files . '/docs/internals.md', 'docs/internals.md'),
];
