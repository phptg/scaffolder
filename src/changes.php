<?php

declare(strict_types=1);

use Phptg\Scaffolder\Change\PrepareGitignore;
use Phptg\Scaffolder\Change\PrepareReadme;
use Phptg\Scaffolder\Change\PrepareRectorConfiguration;
use Phptg\Scaffolder\Fact\UsePhpStan;
use Phptg\Scaffolder\Fact\UsePhpUnit;
use Phptg\Scaffolder\Fact\UsePsalm;
use Vjik\Scaffolder\Change\ChangeIf;
use Vjik\Scaffolder\Change\CopyFile;
use Vjik\Scaffolder\Change\CopyFileIfNotExists;
use Vjik\Scaffolder\Change\PrepareComposerJson;
use Vjik\Scaffolder\Change\WriteLicense\Bsd3ClauseLicense;
use Vjik\Scaffolder\Change\WriteLicense\WriteLicense;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\PackageProject;

$files = dirname(__DIR__) . '/files';

return [
    new PrepareComposerJson(
        customChange: static function (array $new, Context $context): array {
            $project = $context->getFact(PackageProject::class);
            $new['support'] = [
                'issues' => "https://github.com/phptg/$project/issues?state=open",
                'chat' => 'https://t.me/predvoditelev_chat',
                'source' => "https://github.com/phptg/$project",
            ];

            // Composer bin plugin
            $new['require-dev']['bamarni/composer-bin-plugin'] ??= '^1.8.3';
            $new['extra']['bamarni-bin'] = [
                'bin-links' => true,
                'forward-command' => true,
                'target-directory' => 'tools',
            ];
            $new['config']['allow-plugins']['bamarni/composer-bin-plugin'] = true;

            // Rector
            $new['require-dev']['rector/rector'] ??= '^2.3.0';
            $new['scripts']['rector'] = 'rector';

            // Psalm
            if ($context->getFact(UsePsalm::class)) {
                $new['scripts']['psalm'] = 'psalm';
            }

            // PHPStan
            if ($context->getFact(UsePhpStan::class)) {
                $new['require-dev']['phpstan/phpstan'] ??= '^2.1.33';
                $new['scripts']['phpstan'] = 'phpstan analyse -c phpstan.neon';
            }

            // PHPUnit
            if ($context->getFact(UsePhpUnit::class)) {
                $new['require-dev']['phpunit/phpunit'] ??= '^11.5.46';
            }

            return $new;
        }
    ),
    new PrepareGitignore(),
    new CopyFile($files . '/runtime/.gitignore', 'runtime/.gitignore'),
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
    new CopyFile($files . '/tools/.gitignore', 'tools/.gitignore'),
    new ChangeIf(
        new CopyFileIfNotExists($files . '/phpunit.xml.dist', 'phpunit.xml.dist'),
        UsePhpUnit::class,
    ),
    new ChangeIf(
        [
            new CopyFileIfNotExists($files . '/tools/psalm/composer.json', 'tools/psalm/composer.json'),
            new CopyFileIfNotExists($files . '/psalm.xml', 'psalm.xml'),
        ],
        UsePsalm::class,
    ),
    new ChangeIf(
        new CopyFileIfNotExists($files . '/phpstan.neon', 'phpstan.neon'),
        UsePhpStan::class,
    ),
];
