<?php

namespace App\Services\Contracts;

interface SiteStructureGenerator
{
    /** @param array<string,mixed> $brief @return array<string,mixed> */
    public function generate(array $brief, string $locale, int $pageLimit, string $executionProfile = 'fast'): array;
}
