<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Phptg\Scaffolder\Fact\UsePhpUnit;
use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\PackageType;

use function sprintf;

final readonly class PrepareGitignore implements Change
{
    private const FILE = '.gitignore';

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
        $lines = [
            '# Composer',
            '/vendor/',
        ];
        if ($context->getFact(PackageType::class) === 'library') {
            $lines[] = '/composer.lock';
        }
        $lines[] = '';

        if ($context->getFact(UsePhpUnit::class)) {
            $lines[] = '# PHPUnit';
            $lines[] = '/phpunit.xml';
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
