<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\HighestMinorPhpVersion;
use Vjik\Scaffolder\Fact\LowestMinorPhpVersion;
use Vjik\Scaffolder\Fact\PackageProject;
use Vjik\Scaffolder\Fact\PhpConstraintName;
use Vjik\Scaffolder\Fact\Title;
use Vjik\Scaffolder\Value\MinorPhpVersion;

use function sprintf;

final readonly class PrepareReadme implements Change
{
    private const FILE = 'README.md';

    public function decide(Context $context): callable|array|null
    {
        $old = $context->tryReadFile(self::FILE);
        $new = $old ?? $this->createNew($context);

        if ($old === $new) {
            return null;
        }

        return static fn(Cli $cli) => $cli->step(
            sprintf('Write `%s`', self::FILE),
            fn() => $context->writeTextFile(self::FILE, $new),
        );
    }

    private function createNew(Context $context): string
    {
        $title = $context->getFact(Title::class);
        $packageProject = $context->getFact(PackageProject::class);
        $phpConstraint = $this->createPhpConstraint($context);

        return <<<README
            <div align="center">
                <a href="https://github.com/phptg">
                    <img src="logo.png" alt="PHPTG">
                </a>
                <h1 align="center">$title</h1>
                <br>
            </div>

            [![Latest Stable Version](https://poser.pugx.org/phptg/$packageProject/v)](https://packagist.org/packages/phptg/$packageProject)
            [![Total Downloads](https://poser.pugx.org/phptg/$packageProject/downloads)](https://packagist.org/packages/phptg/$packageProject)
            [![Build status](https://github.com/phptg/$packageProject/actions/workflows/build.yml/badge.svg)](https://github.com/phptg/$packageProject/actions/workflows/build.yml)
            [![Coverage Status](https://coveralls.io/repos/github/phptg/$packageProject/badge.svg)](https://coveralls.io/github/phptg/$packageProject)
            [![Mutation score](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fphptg%2F$packageProject%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/phptg/$packageProject/master)
            [![Static analysis](https://github.com/phptg/$packageProject/actions/workflows/static.yml/badge.svg?branch=master)](https://github.com/phptg/$packageProject/actions/workflows/static.yml?query=branch%3Amaster)

            ## Requirements

            - $phpConstraint.

            ## Installation

            The package can be installed with [Composer](https://getcomposer.org/download/):

            ```shell
            composer require phptg/$packageProject
            ```

            ## General usage

            ...

            ## Documentation

            - [Internals](docs/internals.md)

            If you have any questions or problems with this package, use [author telegram chat](https://t.me/predvoditelev_chat) for communication.

            ## License

            The `phptg/$packageProject` is free software. It is released under the terms of the BSD License.
            Please see [`LICENSE`](./LICENSE) for more information.
            README;
    }

    private function createPhpConstraint(Context $context): string
    {
        $result = $context->getFact(PhpConstraintName::class) === 'php-64bit' ? 'PHP (64-bit)' : 'PHP';

        $lowest = $context->getFact(LowestMinorPhpVersion::class);
        if ($lowest === MinorPhpVersion::UNKNOWN) {
            return $result;
        }

        $result .= ' ' . $lowest->value;

        $highest = $context->getFact(HighestMinorPhpVersion::class);
        if ($highest === MinorPhpVersion::UNKNOWN || $highest === $lowest) {
            return $result;
        }

        return $result . ' - ' . $highest->value;
    }
}
