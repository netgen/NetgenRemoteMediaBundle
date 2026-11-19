<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use Netgen\Bundle\RemoteMediaBundle\Translation\VueTranslations;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryProvider;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

use function array_keys;

final class Configuration
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly VueTranslations $vueTranslations,
        private readonly VariationResolver $variationResolver,
        private readonly string $folderMode,
        private readonly bool $useAssetFolderAsPublicIdPrefix,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse([
            'paths' => $this->resolvePaths(),
            'translations' => $this->vueTranslations->translate(),
            'availableVariations' => $this->resolveAvailableVariations($request),
            'allVariations' => $this->resolveAllVariations($request),
            'uploadContext' => $this->resolveUploadContext($request),
            'folderScopedUploads' => $this->folderMode === CloudinaryProvider::FOLDER_MODE_FIXED || $this->useAssetFolderAsPublicIdPrefix,
        ]);
    }

    private function resolvePaths(): array
    {
        return [
            'browse_resources' => $this->router->generate('netgen_remote_media_ajax_resource_browse'),
            'upload_resources' => $this->router->generate('netgen_remote_media_ajax_resource_upload'),
            'load_facets' => $this->router->generate('netgen_remote_media_ajax_facets_load'),
            'load_folders' => $this->router->generate('netgen_remote_media_ajax_folder_load'),
            'create_folder' => $this->router->generate('netgen_remote_media_ajax_folder_create'),
        ];
    }

    private function resolveAvailableVariations(Request $request): array
    {
        $variationGroup = $request->query->get('variationGroup');

        $variations = $this->variationResolver->getAvailableCroppableVariations($variationGroup);

        $availableVariations = [];
        foreach ($variations as $variationName => $variationConfig) {
            foreach ($variationConfig['transformations'] as $name => $config) {
                if ($name !== 'crop') {
                    continue;
                }

                $availableVariations[$variationName] = $config;
            }
        }

        return $availableVariations;
    }

    private function resolveAllVariations(Request $request): array
    {
        $variationGroup = $request->query->get('variationGroup');

        $variations = $this->variationResolver->getAvailableVariations($variationGroup);

        return array_keys($variations);
    }

    /**
     * @return array<string, string>
     */
    private function resolveUploadContext(Request $request): array
    {
        return $request->query->all('uploadContext');
    }
}
