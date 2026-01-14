<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Fact;

use Phptg\Scaffolder\Value\ReadmeBadgesConfig;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputOption;
use Vjik\Scaffolder\Cli;
use Vjik\Scaffolder\Context;
use Vjik\Scaffolder\Fact;
use Vjik\Scaffolder\Params;

/**
 * @extends Fact<ReadmeBadgesConfig>
 */
final class ReadmeBadges extends Fact
{
    public const string PACKAGIST_VERSION_OPTION = 'badge-packagist-version';
    public const string PACKAGIST_DOWNLOADS_OPTION = 'badge-packagist-downloads';
    public const string BUILD_OPTION = 'badge-build';
    public const string COVERAGE_OPTION = 'badge-coverage';
    public const string MUTATION_OPTION = 'badge-mutation';
    public const string PSALM_OPTION = 'badge-psalm';
    public const string PHPSTAN_OPTION = 'badge-phpstan';

    public static function configureCommand(SymfonyCommand $command, Params $params): void
    {
        $command
            ->addOption(
                self::PACKAGIST_VERSION_OPTION,
                mode: InputOption::VALUE_OPTIONAL,
                default: $params->get(self::PACKAGIST_VERSION_OPTION),
            )
            ->addOption(
                self::PACKAGIST_DOWNLOADS_OPTION,
                mode: InputOption::VALUE_OPTIONAL,
                default: $params->get(self::PACKAGIST_DOWNLOADS_OPTION),
            )
            ->addOption(
                self::BUILD_OPTION,
                mode: InputOption::VALUE_OPTIONAL,
                default: $params->get(self::BUILD_OPTION),
            )
            ->addOption(
                self::COVERAGE_OPTION,
                mode: InputOption::VALUE_OPTIONAL,
                default: $params->get(self::COVERAGE_OPTION),
            )
            ->addOption(
                self::MUTATION_OPTION,
                mode: InputOption::VALUE_OPTIONAL,
                default: $params->get(self::MUTATION_OPTION),
            )
            ->addOption(
                self::PSALM_OPTION,
                mode: InputOption::VALUE_OPTIONAL,
                default: $params->get(self::PSALM_OPTION),
            )
            ->addOption(
                self::PHPSTAN_OPTION,
                mode: InputOption::VALUE_OPTIONAL,
                default: $params->get(self::PHPSTAN_OPTION),
            );
    }

    public static function resolve(Cli $cli, Context $context): mixed
    {
        return new ReadmeBadgesConfig(
            packagistVersion: self::getBoolOption($cli, self::PACKAGIST_VERSION_OPTION, static fn() => true),
            packagistDownloads: self::getBoolOption($cli, self::PACKAGIST_DOWNLOADS_OPTION, static fn() => true),
            build: self::getBoolOption(
                $cli,
                self::BUILD_OPTION,
                static fn() => $context->getFact(UsePhpUnit::class),
            ),
            coverage: self::getBoolOption(
                $cli,
                self::COVERAGE_OPTION,
                static fn() => $context->getFact(UsePhpUnit::class),
            ),
            mutation: self::getBoolOption(
                $cli,
                self::MUTATION_OPTION,
                static fn() => $context->getFact(UseInfection::class),
            ),
            psalm: self::getBoolOption(
                $cli,
                self::PSALM_OPTION,
                static fn() => $context->getFact(UsePsalm::class),
            ),
            phpstan: self::getBoolOption(
                $cli,
                self::PHPSTAN_OPTION,
                static fn() => $context->getFact(UsePhpStan::class),
            ),
        );
    }

    /**
     * @param callable(): bool $default
     */
    private static function getBoolOption(Cli $cli, string $option, callable $default): bool
    {
        $value = $cli->getOption($option);
        return $value === null ? $default() : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
