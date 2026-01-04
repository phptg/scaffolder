<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Fact;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputOption;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact;
use Vjik\Scaffolder\Params;

/**
 * @extends Fact<bool>
 */
final class UseComposerDependencyAnalyser extends Fact
{
    public const string VALUE_OPTION = 'use-composer-dependency-analyser';

    public static function configureCommand(SymfonyCommand $command, Params $params): void
    {
        $command->addOption(
            self::VALUE_OPTION,
            mode: InputOption::VALUE_OPTIONAL,
            default: $params->get(self::VALUE_OPTION, true),
        );
    }

    public static function resolve(Cli $cli, Context $context): mixed
    {
        return filter_var($cli->getOption(self::VALUE_OPTION), FILTER_VALIDATE_BOOLEAN);
    }
}
