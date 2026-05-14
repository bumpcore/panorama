<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Contracts;

interface QueryBackedModel
{
    /**
     * @param array<string, mixed> $params
     */
    public function buildPanoramaSource(array $params): mixed;

    public function panoramaSourceAlias(): string;
}
