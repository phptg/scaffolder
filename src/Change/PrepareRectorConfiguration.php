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

final readonly class PrepareRectorConfiguration implements Change
{
    private const string FILE = 'rector.php';

    public function decide(Context $context): callable|array|null
    {
        $old = $context->tryReadFile(self::FILE);
        $new = $old ?? file_get_contents(dirname(self::FILE, 2) . '/files/' . self::FILE);

        $phpSet = $this->getPhpSet($context);
        if ($phpSet === null) {
            return null;
        }

        $new = preg_replace(
            '/->withPhpSets\([^()]*\)/',
            "->withPhpSets($phpSet: true)",
            $new,
        );

        if ($old === $new) {
            return null;
        }

        return static fn(Cli $cli) => $cli->step(
            sprintf('Write `%s`', self::FILE),
            fn() => $context->writeTextFile(self::FILE, $new),
        );
    }

    private function getPhpSet(Context $context): ?string
    {
        $phpVersion = $context->getFact(LowestMinorPhpVersion::class);

        return match ($phpVersion) {
            MinorPhpVersion::PHP82 => 'php82',
            MinorPhpVersion::PHP83 => 'php83',
            MinorPhpVersion::PHP84 => 'php84',
            MinorPhpVersion::PHP85 => 'php85',
            default => null,
        };
    }
}
