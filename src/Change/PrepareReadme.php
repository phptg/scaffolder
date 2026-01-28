<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Phptg\Scaffolder\Fact\ReadmeBadges;
use Phptg\Scaffolder\Fact\UpdateReadmeBadges;
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
    private const string FILE = 'README.md';

    public function decide(Context $context): callable|array|null
    {
        $original = $context->tryReadFile(self::FILE);

        if ($original === null) {
            $new = $this->createNew($context);
        } else {
            $new = $original;
            if ($context->getFact(UpdateReadmeBadges::class)) {
                $badges = $this->createBadges($context);
                /** @var string $new */
                $new = preg_replace('~(?:^\[!\[.*\]\(.*\)\]\(.*\)\s*$\n)+~m', "$badges\n\n", $new, limit: 1);
            }
        }

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
        $title = $context->getFact(Title::class);
        $packageProject = $context->getFact(PackageProject::class);
        $phpConstraint = $this->createPhpConstraint($context);
        $badges = $this->createBadges($context);

        return <<<README
            <div align="center">
                <a href="https://github.com/phptg">
                    <img src="logo.png" alt="PHPTG">
                </a>
                <h1 align="center">$title</h1>
                <br>
            </div>

            $badges

            > [!IMPORTANT]
            > This project is developed and maintained by [Sergei Predvoditelev](https://github.com/vjik).
            > Community support helps keep the project actively developed and well maintained.
            > You can support the project using the following services:
            >
            > - [Boosty](https://boosty.to/vjik)
            > - [CloudTips](https://pay.cloudtips.ru/p/192ce69b)
            >
            > Thank you for your support ❤️

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

    private function createBadges(Context $context): string
    {
        $packageProject = $context->getFact(PackageProject::class);
        $badgesConfig = $context->getFact(ReadmeBadges::class);

        $badges = [];

        if ($badgesConfig->packagistVersion) {
            $badges[] = "[![Latest Stable Version](https://poser.pugx.org/phptg/$packageProject/v)](https://packagist.org/packages/phptg/$packageProject)";
        }

        if ($badgesConfig->packagistDownloads) {
            $badges[] = "[![Total Downloads](https://poser.pugx.org/phptg/$packageProject/downloads)](https://packagist.org/packages/phptg/$packageProject)";
        }

        if ($badgesConfig->build) {
            $badges[] = "[![Build status](https://github.com/phptg/$packageProject/actions/workflows/build.yml/badge.svg)](https://github.com/phptg/$packageProject/actions/workflows/build.yml)";
        }

        if ($badgesConfig->coverage) {
            $badges[] = "[![Coverage Status](https://coveralls.io/repos/github/phptg/$packageProject/badge.svg)](https://coveralls.io/github/phptg/$packageProject)";
        }

        if ($badgesConfig->mutation) {
            $badges[] = "[![Mutation score](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fphptg%2F$packageProject%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/phptg/$packageProject/master)";
        }

        if ($badgesConfig->psalm) {
            $badges[] = "[![Static analysis](https://github.com/phptg/$packageProject/actions/workflows/psalm.yml/badge.svg?branch=master)](https://github.com/phptg/$packageProject/actions/workflows/psalm.yml?query=branch%3Amaster)";
        }

        if ($badgesConfig->phpstan) {
            $badges[] = "[![Static analysis](https://github.com/phptg/$packageProject/actions/workflows/phpstan.yml/badge.svg?branch=master)](https://github.com/phptg/$packageProject/actions/workflows/phpstan.yml?query=branch%3Amaster)";
        }

        return implode("\n", $badges);
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
