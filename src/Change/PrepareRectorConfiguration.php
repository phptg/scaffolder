<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use Composer\Semver\Constraint\Constraint;
use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\PhpConstraint;

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
        $constraint = $context->getFact(PhpConstraint::class);

        return array_find_key(
            [
                'php82' => '8.2.9999999',
                'php83' => '8.3.9999999',
                'php84' => '8.4.9999999',
                'php85' => '8.5.9999999',
            ],
            static fn($version) => $constraint->matches(new Constraint('==', $version))
        );
    }
}
