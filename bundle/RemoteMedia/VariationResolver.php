<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

class VariationResolver
{
    protected $variations = [];

    /**
     * Dynamic injection of configured transformations.
     *
     * @param array $variations
     */
    public function setVariations($variations = [])
    {
        $this->variations = $variations;
    }

    /**
     * Return merged variations defined for a provided content type and default ones.
     *
     * @param string $contentTypeIdentifier
     *
     * @return array
     */
    public function getVariationsForContentType($contentTypeIdentifier)
    {
        $defaultVariations = isset($this->variations['default']) ? $this->variations['default'] : [];
        $contentTypeVariations = isset($this->variations[$contentTypeIdentifier]) ?
            $this->variations[$contentTypeIdentifier] : [];

        return array_merge($defaultVariations, $contentTypeVariations);
    }

    /**
     * Returns variations for a provided content type which have 'crop' transformation configured.
     *
     * @param $contentTypeIdentifier
     *
     * @return array
     */
    public function getCroppbableVariations($contentTypeIdentifier)
    {
        $variations = $this->getVariationsForContentType($contentTypeIdentifier);

        $croppableVariations = [];
        foreach ($variations as $variationName => $variationOptions) {
            if (isset($variationOptions['transformations']['crop'])) {
                $croppableVariations[$variationName] = $variationOptions;
            }
        }

        return $croppableVariations;
    }

    /**
     * Returns variations to be used when embedding image into ezxml text.
     *
     * @return array
     */
    public function getEmbedVariations()
    {
        $variations = isset($this->variations['embedded']) ?
            $this->variations['embedded'] : [];

        $croppableVariations = [];
        foreach ($variations as $variationName => $variationOptions) {
            if (isset($variationOptions['transformations']['crop'])) {
                $croppableVariations[$variationName] = $variationOptions;
            }
        }

        return $croppableVariations;
    }
}
