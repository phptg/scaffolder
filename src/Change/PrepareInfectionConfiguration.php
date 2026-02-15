<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Change;

use JsonException;
use Vjik\Scaffolder\Change;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact\SourceDirectory;

use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

final readonly class PrepareInfectionConfiguration implements Change
{
    private const string FILE = 'infection.json.dist';

    /**
     * @throws JsonException
     */
    public function decide(Context $context): callable|array|null
    {
        $originalContent = $context->tryReadFile(self::FILE);

        $original = $new = $originalContent === null
            ? []
            : json_decode($originalContent, true, flags: JSON_THROW_ON_ERROR);

        $sourceDirectory = rtrim($context->getFact(SourceDirectory::class), '/');
        $new['source'] = ['directories' => [$sourceDirectory]];
        $new['logs'] = [
            'text' => 'runtime/infection/run.log',
            'stryker' => [
                'report' => 'master',
            ],
        ];
        $new['tmpDir'] = 'runtime/infection';
        $new['mutators']['@default'] = true;

        if ($original === $new) {
            return null;
        }

        $newContent = json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        return static fn(Cli $cli) => $cli->step(
            sprintf('Write `%s`', self::FILE),
            fn() => $context->writeTextFile(self::FILE, $newContent),
        );
    }
}
