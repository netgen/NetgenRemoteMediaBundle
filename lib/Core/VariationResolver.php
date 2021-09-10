<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core;

use function array_merge;

final class VariationResolver
{
    private array $variations = [];

    public function setVariations(array $variations = [])
    {
        $this->variations = $variations;
    }

    public function getVariationsForGroup(string $group): array
    {
        $defaultVariations = $this->variations['default'] ?? [];
        $contentTypeVariations = $this->variations[$group] ?? [];

        return array_merge($defaultVariations, $contentTypeVariations);
    }

    public function getCroppbableVariations(string $group): array
    {
        $variations = $this->getVariationsForGroup($group);

        $croppableVariations = [];
        foreach ($variations as $variationName => $variationOptions) {
            if (isset($variationOptions['transformations']['crop'])) {
                $croppableVariations[$variationName] = $variationOptions;
            }
        }

        return $croppableVariations;
    }
}
