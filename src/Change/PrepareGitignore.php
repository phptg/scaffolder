<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

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
        $composerLock = $context->getFact(PackageType::class) === 'library' ? "\n/composer.lock" : '';
        return <<<GITIGNORE
            # Composer
            /vendor/$composerLock

            # PHPUnit
            /phpunit.xml
            GITIGNORE;
    }
}
