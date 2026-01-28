<?php

declare(strict_types=1);

use Phptg\Scaffolder\Change\PrepareChangelog;
use Phptg\Scaffolder\Change\PrepareDocsInternals;
use Phptg\Scaffolder\Change\PrepareGitHubWorkflowBuild;
use Phptg\Scaffolder\Change\PrepareGitHubWorkflowCodeStyle;
use Phptg\Scaffolder\Change\PrepareGitHubWorkflowComposerDependencyAnalyser;
use Phptg\Scaffolder\Change\PrepareGitHubWorkflowMutation;
use Phptg\Scaffolder\Change\PrepareGitHubWorkflowPhpstan;
use Phptg\Scaffolder\Change\PrepareGitHubWorkflowPsalm;
use Phptg\Scaffolder\Change\PrepareGitignore;
use Phptg\Scaffolder\Change\PrepareReadme;
use Phptg\Scaffolder\Change\PrepareRectorConfiguration;
use Phptg\Scaffolder\Fact\UseComposerDependencyAnalyser;
use Phptg\Scaffolder\Fact\UseInfection;
use Phptg\Scaffolder\Fact\UsePhpStan;
use Phptg\Scaffolder\Fact\UsePhpUnit;
use Phptg\Scaffolder\Fact\UsePsalm;
use Vjik\Scaffolder\Change\ChangeIf;
use Vjik\Scaffolder\Change\CopyFile;
use Vjik\Scaffolder\Change\CopyFileIfNotExists;
use Vjik\Scaffolder\Change\EnsureDirectoryWithGitkeep;
use Vjik\Scaffolder\Change\PrepareComposerJson;
use Vjik\Scaffolder\Change\WriteLicense\Bsd3ClauseLicense;
use Vjik\Scaffolder\Change\WriteLicense\WriteLicense;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\PackageProject;
use Vjik\Scaffolder\Fact\SourceDirectory;
use Vjik\Scaffolder\Fact\TestsDirectory;

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
            $new['funding'] = [
                [
                    'type' => 'cloudtips',
                    'url' => 'https://pay.cloudtips.ru/p/192ce69b',
                ],
                [
                    'type' => 'boosty',
                    'url' => 'https://boosty.to/vjik',
                ],
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

            // PHP CS Fixer
            $new['scripts']['cs-fix'] ??= 'php-cs-fixer fix';

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

            // Composer Dependency Analyser
            if ($context->getFact(UseComposerDependencyAnalyser::class)) {
                $new['scripts']['dependency-analyser'] ??= 'composer-dependency-analyser';
            }

            // Infection
            if ($context->getFact(UseInfection::class)) {
                $new['scripts']['infection'] ??= 'infection --threads=max';
            }

            return $new;
        },
    ),
    new EnsureDirectoryWithGitkeep(SourceDirectory::class),
    'tests-directory' => new EnsureDirectoryWithGitkeep(TestsDirectory::class),
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
    'docs-internals' => new PrepareDocsInternals(),
    'changelog' => new PrepareChangelog(),
    new CopyFile($files . '/tools/.gitignore', 'tools/.gitignore'),
    new ChangeIf(
        [
            new CopyFileIfNotExists($files . '/phpunit.xml.dist', 'phpunit.xml.dist'),
            new PrepareGitHubWorkflowBuild(),
        ],
        UsePhpUnit::class,
    ),
    new ChangeIf(
        [
            new CopyFileIfNotExists($files . '/tools/psalm/composer.json', 'tools/psalm/composer.json'),
            new CopyFileIfNotExists($files . '/psalm.xml', 'psalm.xml'),
            new PrepareGitHubWorkflowPsalm(),
        ],
        UsePsalm::class,
    ),
    new ChangeIf(
        [
            new CopyFileIfNotExists($files . '/phpstan.neon', 'phpstan.neon'),
            new PrepareGitHubWorkflowPhpstan(),
        ],
        UsePhpStan::class,
    ),
    new CopyFileIfNotExists($files . '/tools/php-cs-fixer/composer.json', 'tools/php-cs-fixer/composer.json'),
    new CopyFileIfNotExists($files . '/.php-cs-fixer.dist.php', '.php-cs-fixer.dist.php'),
    new PrepareGitHubWorkflowCodeStyle(),
    new ChangeIf(
        [
            new CopyFileIfNotExists($files . '/tools/composer-dependency-analyser/composer.json', 'tools/composer-dependency-analyser/composer.json'),
            new CopyFileIfNotExists($files . '/composer-dependency-analyser.php', 'composer-dependency-analyser.php'),
            new PrepareGitHubWorkflowComposerDependencyAnalyser(),
        ],
        UseComposerDependencyAnalyser::class,
    ),
    new ChangeIf(
        [
            new CopyFileIfNotExists($files . '/tools/infection/composer.json', 'tools/infection/composer.json'),
            new CopyFileIfNotExists($files . '/infection.json.dist', 'infection.json.dist'),
            new PrepareGitHubWorkflowMutation(),
        ],
        UseInfection::class,
    ),
    new CopyFileIfNotExists($files . '/.gitattributes', '.gitattributes'),
];
