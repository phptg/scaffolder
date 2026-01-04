<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\Title;

use function sprintf;

final readonly class PrepareChangelog implements Change
{
    private const FILE = 'CHANGELOG.md';

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
        $title = $context->getFact(Title::class);

        return <<<CHANGELOG
            # $title Change Log

            ## under development

            - Initial release.
            CHANGELOG;
    }
}
