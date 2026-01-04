<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\LowestMinorPhpVersion;
use Vjik\Scaffolder\Value\MinorPhpVersion;

use function dirname;
use function sprintf;

final readonly class PrepareGitHubWorkflowCodeStyle implements Change
{
    private const FILE = '.github/workflows/code-style.yml';

    public function decide(Context $context): callable|array|null
    {
        $original = $context->tryReadFile(self::FILE);

        /** @var string $new */
        $new = $original ?? file_get_contents(dirname(__DIR__, 2) . '/files/' . self::FILE);

        $phpVersion = $context->getFact(LowestMinorPhpVersion::class);
        if ($phpVersion !== MinorPhpVersion::UNKNOWN) {
            /** @var string $new */
            $new = preg_replace(
                '/^(\s*php-version:\s*)(["\']?)[\d\.]+\2/m',
                '${1}' . $phpVersion->value,
                $new,
                1,
            );
        }

        if ($original === $new) {
            return null;
        }

        return static fn(Cli $cli) => $cli->step(
            sprintf('Write `%s`', self::FILE),
            fn() => $context->writeTextFile(self::FILE, $new),
        );
    }
}
