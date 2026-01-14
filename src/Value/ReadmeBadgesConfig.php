<?php

declare(strict_types=1);

namespace Phptg\Scaffolder\Value;

final readonly class ReadmeBadgesConfig
{
    public function __construct(
        public bool $packagistVersion,
        public bool $packagistDownloads,
        public bool $build,
        public bool $coverage,
        public bool $mutation,
        public bool $psalm,
        public bool $phpstan,
    ) {}
}
