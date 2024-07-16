<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller;

use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Configuration extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly VariationResolver $variationResolver,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse([
            'paths' => $this->resolvePaths(),
            'translations' => $this->resolveTranslations(),
            'availableVariations' => $this->resolveAvailableVariations($request),
            'allVariations' => $this->resolveAllVariations($request),
            'uploadContext' => $this->resolveUploadContext($request),
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

    private function resolveTranslations(): array
    {
        return [
            'browse_title' => $this->translator->trans('ngrm.edit.vue.browse.title', [], 'ngremotemedia'),
            'browse_select_type' => $this->translator->trans('ngrm.edit.vue.browse.facets.select_type', [], 'ngremotemedia'),
            'browse_loading_types' => $this->translator->trans('ngrm.edit.vue.browse.facets.loading_types', [], 'ngremotemedia'),
            'browse_all_types' => $this->translator->trans('ngrm.edit.vue.browse.facets.all_types', [], 'ngremotemedia'),
            'browse_select_folder' => $this->translator->trans('ngrm.edit.vue.browse.facets.select_folder', [], 'ngremotemedia'),
            'browse_loading_folders' => $this->translator->trans('ngrm.edit.vue.browse.facets.loading_folders', [], 'ngremotemedia'),
            'browse_all_folders' => $this->translator->trans('ngrm.edit.vue.browse.facets.all_folders', [], 'ngremotemedia'),
            'browse_select_tag' => $this->translator->trans('ngrm.edit.vue.browse.facets.select_tag', [], 'ngremotemedia'),
            'browse_loading_tags' => $this->translator->trans('ngrm.edit.vue.browse.facets.loading_tags', [], 'ngremotemedia'),
            'browse_all_tags' => $this->translator->trans('ngrm.edit.vue.browse.facets.all_tags', [], 'ngremotemedia'),
            'browse_select_visibility' => $this->translator->trans('ngrm.edit.vue.browse.facets.select_visibility', [], 'ngremotemedia'),
            'browse_loading_visibilities' => $this->translator->trans('ngrm.edit.vue.browse.facets.loading_visibilities', [], 'ngremotemedia'),
            'browse_all_visibilities' => $this->translator->trans('ngrm.edit.vue.browse.facets.all_visibilities', [], 'ngremotemedia'),
            'search' => $this->translator->trans('ngrm.edit.vue.browse.facets.search', [], 'ngremotemedia'),
            'search_placeholder' => $this->translator->trans('ngrm.edit.vue.browse.facets.search_placeholder', [], 'ngremotemedia'),
            'browse_empty_folder' => $this->translator->trans('ngrm.edit.vue.browse.empty_folder', [], 'ngremotemedia'),
            'browse_empty_folder_hint' => $this->translator->trans('ngrm.edit.vue.browse.empty_folder_hint', [], 'ngremotemedia'),
            'browse_select' => $this->translator->trans('ngrm.edit.vue.browse.select', [], 'ngremotemedia'),
            'load_more' => $this->translator->trans('ngrm.edit.vue.browse.load_more', [], 'ngremotemedia'),
            'crop_modal_title' => $this->translator->trans('ngrm.edit.vue.crop.modal_title', [], 'ngremotemedia'),
            'crop_reset' => $this->translator->trans('ngrm.edit.vue.crop.reset', [], 'ngremotemedia'),
            'crop_apply' => $this->translator->trans('ngrm.edit.vue.crop.apply', [], 'ngremotemedia'),
            'crop_cancel' => $this->translator->trans('ngrm.edit.vue.crop.cancel', [], 'ngremotemedia'),
            'crop_add' => $this->translator->trans('ngrm.edit.vue.crop.add', [], 'ngremotemedia'),
            'crop_preview' => $this->translator->trans('ngrm.edit.vue.crop.preview', [], 'ngremotemedia'),
            'crop_save_sizes' => $this->translator->trans('ngrm.edit.vue.crop.save_sizes', [], 'ngremotemedia'),
            'crop_add_size' => $this->translator->trans('ngrm.edit.vue.crop.add_size', [], 'ngremotemedia'),
            'crop_added_for_confirmation' => $this->translator->trans('ngrm.edit.vue.crop.added_for_confirmation', [], 'ngremotemedia'),
            'crop_media_too_small' => $this->translator->trans('ngrm.edit.vue.crop.media_too_small', [], 'ngremotemedia'),
            'media_gallery_empty_folder' => $this->translator->trans('ngrm.edit.vue.media_gallery.empty_folder', [], 'ngremotemedia'),
            'media_gallery_upload_media' => $this->translator->trans('ngrm.edit.vue.media_gallery.upload_media', [], 'ngremotemedia'),
            'media_gallery_select' => $this->translator->trans('ngrm.edit.vue.media_gallery.select', [], 'ngremotemedia'),
            'media_gallery_load_more' => $this->translator->trans('ngrm.edit.vue.media_gallery.load_more', [], 'ngremotemedia'),
            'Search for media' => $this->translator->trans("Search for media", [], 'ngremotemedia'),
            'Load more' => $this->translator->trans("Load more", [], 'ngremotemedia'),
            'Upload new media' => $this->translator->trans('Upload new media', [], 'ngremotemedia'),
            'No results' => $this->translator->trans('No results', [], 'ngremotemedia'),
            'Alternate text' => $this->translator->trans('Alternate text', [], 'ngremotemedia'),
            'Class' => $this->translator->trans('CSS class', [], 'ngremotemedia'),
            'Create new folder?' => $this->translator->trans('Create new folder?', [], 'ngremotemedia'),
            'Folder' => $this->translator->trans('Folder', [], 'ngremotemedia'),
            'All' => $this->translator->trans('All', [], 'ngremotemedia'),
            'Add tag' => $this->translator->trans('Add tag', [], 'ngremotemedia'),
            'Media type' => $this->translator->trans('Media type', [], 'ngremotemedia'),
            'Image' => $this->translator->trans('Image and documents', [], 'ngremotemedia'),
            'Video' => $this->translator->trans('Video', [], 'ngremotemedia'),
            'Loading...' => $this->translator->trans('Loading...', [], 'ngremotemedia'),
            'Cancel' => $this->translator->trans('Cancel', [], 'ngremotemedia'),
            'Save all' => $this->translator->trans('Save all', [], 'ngremotemedia'),
            'Generate' => $this->translator->trans('Generate', [], 'ngremotemedia'),
            'Caption' => $this->translator->trans('Caption', [], 'ngremotemedia'),
            'by' => $this->translator->trans('by', [], 'ngremotemedia'),
            'name' => $this->translator->trans('name', [], 'ngremotemedia'),
            'tag' => $this->translator->trans('tag', [], 'ngremotemedia'),
            'Image is to small for this version' => $this->translator->trans('Image is to small for this version', [], 'ngremotemedia'),
            'close' => $this->translator->trans('Close', [], 'ngremotemedia'),
            'next' => $this->translator->trans('Next 25 &gt;', [], 'ngremotemedia'),
            'prev' => $this->translator->trans('&lt; Previous 25', [], 'ngremotemedia'),
            'interactions_scale' => $this->translator->trans('ngrm.edit.vue.interactions.scale', [], 'ngremotemedia'),
            'interactions_no_media_selected' => $this->translator->trans('ngrm.edit.vue.interactions.no_media_selected', [], 'ngremotemedia'),
            'interactions_remove_media' => $this->translator->trans('ngrm.edit.vue.interactions.remove_media', [], 'ngremotemedia'),
            'interactions_select_media' => $this->translator->trans('ngrm.edit.vue.interactions.select_media', [], 'ngremotemedia'),
            'interactions_manage_media' => $this->translator->trans('ngrm.edit.vue.interactions.manage_media', [], 'ngremotemedia'),
            'interactions_quick_upload' => $this->translator->trans('ngrm.edit.vue.interactions.quick_upload', [], 'ngremotemedia'),
            'preview_alternate_text' => $this->translator->trans('ngrm.edit.vue.preview.alternate_text', [], 'ngremotemedia'),
            'preview_alternate_text_info' => $this->translator->trans('ngrm.edit.vue.preview.alternate_text_info', [], 'ngremotemedia'),
            'preview_caption' => $this->translator->trans('ngrm.edit.vue.preview.caption', [], 'ngremotemedia'),
            'preview_caption_info' => $this->translator->trans('ngrm.edit.vue.preview.caption_info', [], 'ngremotemedia'),
            'preview_tags' => $this->translator->trans('ngrm.edit.vue.preview.tags', [], 'ngremotemedia'),
            'preview_tags_info' => $this->translator->trans('ngrm.edit.vue.preview.tags_info', [], 'ngremotemedia'),
            'preview_watermark_text' => $this->translator->trans('ngrm.edit.vue.preview.watermark_text', [], 'ngremotemedia'),
            'preview_watermark_text_info' => $this->translator->trans('ngrm.edit.vue.preview.watermark_text_info', [], 'ngremotemedia'),
            'preview_css_class' => $this->translator->trans('ngrm.edit.vue.preview.css_class', [], 'ngremotemedia'),
            'preview_css_class_info' => $this->translator->trans('ngrm.edit.vue.preview.css_class_info', [], 'ngremotemedia'),
            'preview_selected_variation' => $this->translator->trans('ngrm.edit.vue.preview.selected_variation', [], 'ngremotemedia'),
            'preview_selected_variation_info' => $this->translator->trans('ngrm.edit.vue.preview.selected_variation_info', [], 'ngremotemedia'),
            'preview_size' => $this->translator->trans('ngrm.edit.vue.preview.size', [], 'ngremotemedia'),
            'upload_modal_title' => $this->translator->trans('ngrm.edit.vue.upload.modal_title', [], 'ngremotemedia'),
            'upload_breadcrumbs_info' => $this->translator->trans('ngrm.edit.vue.upload.breadcrumbs_info', [], 'ngremotemedia'),
            'upload_root_folder' => $this->translator->trans('ngrm.edit.vue.upload.root_folder', [], 'ngremotemedia'),
            'upload_info_text' => $this->translator->trans('ngrm.edit.vue.upload.info_text', [], 'ngremotemedia'),
            'upload_button_select' => $this->translator->trans('ngrm.edit.vue.upload.button.select', [], 'ngremotemedia'),
            'upload_button_create' => $this->translator->trans('ngrm.edit.vue.upload.button.create', [], 'ngremotemedia'),
            'upload_button_upload' => $this->translator->trans('ngrm.edit.vue.upload.button.upload', [], 'ngremotemedia'),
            'upload_button_use_existing_resource' => $this->translator->trans('ngrm.edit.vue.upload.button.use_existing_resource', [], 'ngremotemedia'),
            'upload_checkbox_overwrite' => $this->translator->trans('ngrm.edit.vue.upload.checkbox.overwrite', [], 'ngremotemedia'),
            'upload_placeholder_new_folder' => $this->translator->trans('ngrm.edit.vue.upload.placeholder.new_folder', [], 'ngremotemedia'),
            'upload_error_existing_resource' => $this->translator->trans('ngrm.edit.vue.upload.error.existing_resource', [], 'ngremotemedia'),
            'upload_error_unsupported_resource_type' => $this->translator->trans('ngrm.edit.vue.upload.error.unsupported_resource_type', [], 'ngremotemedia'),
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

        return array_keys($variations);;
    }

    /**
     * @return array<string, string>
     */
    private function resolveUploadContext(Request $request): array
    {
        return $request->query->all();
    }
}
