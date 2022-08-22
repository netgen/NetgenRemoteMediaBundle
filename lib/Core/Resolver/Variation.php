<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Resolver;

use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use Netgen\RemoteMedia\Exception\CropSettingsNotFoundException;
use Netgen\RemoteMedia\Exception\TransformationHandlerFailedException;
use Netgen\RemoteMedia\Exception\TransformationHandlerNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function array_merge;

final class Variation
{
    private Registry $registry;

    private LoggerInterface $logger;

    private array $variations = [];

    public function setVariations(array $variations = [])
    {
        $this->variations = $variations;
    }

    public function setServices(Registry $registry, ?LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->logger = $logger ?? new NullLogger();
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

    /**
     * Builds transformation options for the provider to consume.
     *
     * @return array options of the total sum of transformations for the provider to use
     */
    public function processConfiguredVariation(
        RemoteResourceLocation $location,
        string $providerIdentifier,
        string $variationGroup,
        string $variationName
    ): array {
        $configuredVariations = $this->getVariationsForGroup($variationGroup);

        $options = [];

        if (!isset($configuredVariations[$variationName])) {
            return $options;
        }

        $variationConfiguration = $configuredVariations[$variationName];
        foreach ($variationConfiguration['transformations'] as $transformationIdentifier => $config) {
            if ($transformationIdentifier === 'crop') {
                try {
                    $cropSettings = $location->getCropSettingsForVariation($variationName);

                    $config = [
                        $cropSettings->getX(),
                        $cropSettings->getY(),
                        $cropSettings->getWidth(),
                        $cropSettings->getHeight(),
                    ];
                } catch (CropSettingsNotFoundException $exception) {
                    continue;
                }
            }

            try {
                $transformationHandler = $this->registry->getHandler(
                    $transformationIdentifier,
                    $providerIdentifier,
                );
            } catch (TransformationHandlerNotFoundException $exception) {
                $this->logger->notice('[NGRM] ' . $exception->getMessage());

                continue;
            }

            try {
                $options[] = $transformationHandler->process($config);
            } catch (TransformationHandlerFailedException $exception) {
                $this->logger->notice('[NGRM] ' . $exception->getMessage());

                continue;
            }
        }

        return $options;
    }
}
