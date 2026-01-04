<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\HighestMinorPhpVersion;
use Vjik\Scaffolder\Value\MinorPhpVersion;

use function dirname;
use function sprintf;

final readonly class PrepareGitHubWorkflowMutation implements Change
{
    private const FILE = '.github/workflows/mutation.yml';

    public function decide(Context $context): callable|array|null
    {
        $original = $context->tryReadFile(self::FILE);

        /** @var string $new */
        $new = $original ?? file_get_contents(dirname(__DIR__, 2) . '/files/' . self::FILE);

        $phpVersion = $context->getFact(HighestMinorPhpVersion::class);
        if ($phpVersion === MinorPhpVersion::UNKNOWN) {
            return null;
        }

        $phpMatrix = '          - "' . $phpVersion->value . '"';

        /** @var string $new */
        $new = preg_replace('/^(\s*php:\n)(?:\s*-\s*".*"\n)+/m', "$1$phpMatrix\n", $new, 1);

        if ($original === $new) {
            return null;
        }

        return static fn(Cli $cli) => $cli->step(
            sprintf('Write `%s`', self::FILE),
            fn() => $context->writeTextFile(self::FILE, $new),
        );
    }
}
