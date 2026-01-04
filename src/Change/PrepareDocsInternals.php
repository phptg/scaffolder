<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Phptg\Scaffolder\Fact\UseComposerDependencyAnalyser;
use Phptg\Scaffolder\Fact\UsePhpStan;
use Phptg\Scaffolder\Fact\UsePhpUnit;
use Phptg\Scaffolder\Fact\UsePsalm;
use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;

use function sprintf;

final readonly class PrepareDocsInternals implements Change
{
    private const FILE = 'docs/internals.md';

    public function decide(Context $context): callable|array|null
    {
        $original = $context->tryReadFile(self::FILE);
        $new = $original ?? $this->createNew($context);

        if ($original === $new) {
            return null;
        }

        return static fn(Cli $cli) => $cli->step(
            sprintf('Write `%s`', self::FILE),
            fn() => $context->writeTextFile(self::FILE, $new),
        );
    }

    private function createNew(Context $context): string
    {
        $blocks = ['# Internals'];

        if ($context->getFact(UsePhpUnit::class)) {
            $blocks[] = <<<BLOCK
                ## Unit testing

                The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

                ```shell
                ./vendor/bin/phpunit
                ```
                BLOCK;
        }

        if ($context->getFact(UsePsalm::class)) {
            $blocks[] = <<<BLOCK
                ## Static analysis

                The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

                ```shell
                composer psalm
                ```
                BLOCK;
        }

        if ($context->getFact(UsePhpStan::class)) {
            $blocks[] = <<<BLOCK
                ## Static analysis

                The code is statically analyzed with [PHPStan](https://phpstan.org/). To run static analysis:

                ```shell
                composer phpstan
                ```
                BLOCK;
        }

        $blocks[] = <<<BLOCK
            ## Code style

            Package used [PHP CS Fixer](https://cs.symfony.com/) to maintain [PER CS 3.0](https://www.php-fig.org/per/coding-style/)
            code style. To check and fix code style:

            ```shell
            composer cs-fix
            ```
            BLOCK;

        if ($context->getFact(UseComposerDependencyAnalyser::class)) {
            $blocks[] = <<<BLOCK
                ## Dependencies

                Use [Composer Dependency Analyser](https://github.com/shipmonk-rnd/composer-dependency-analyser) to
                detect [Composer](https://getcomposer.org) dependency issues (unused dependencies, shadow dependencies,
                misplaced dependencies):

                ```shell
                composer dependency-analyser
                ```
                BLOCK;
        }

        return implode("\n\n", $blocks);
    }
}
