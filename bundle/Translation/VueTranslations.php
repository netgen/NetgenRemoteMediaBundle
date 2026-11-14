<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Translation;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The one list of translations the Vue app reads from `config.translations`.
 *
 * The form field templates and the CKEditor configuration endpoint both build
 * their translation map from this list, so the two cannot diverge.
 */
final class VueTranslations
{
    public const DOMAIN = 'ngremotemedia';

    /**
     * Key read by the Vue app => message id in the "ngremotemedia" domain.
     */
    public const MESSAGES = [
        'browse_title' => 'ngrm.edit.vue.browse.title',
        'browse_select_type' => 'ngrm.edit.vue.browse.facets.select_type',
        'browse_loading_types' => 'ngrm.edit.vue.browse.facets.loading_types',
        'browse_all_types' => 'ngrm.edit.vue.browse.facets.all_types',
        'browse_select_folder' => 'ngrm.edit.vue.browse.facets.select_folder',
        'browse_loading_folders' => 'ngrm.edit.vue.browse.facets.loading_folders',
        'browse_all_folders' => 'ngrm.edit.vue.browse.facets.all_folders',
        'browse_select_tag' => 'ngrm.edit.vue.browse.facets.select_tag',
        'browse_loading_tags' => 'ngrm.edit.vue.browse.facets.loading_tags',
        'browse_all_tags' => 'ngrm.edit.vue.browse.facets.all_tags',
        'browse_select_visibility' => 'ngrm.edit.vue.browse.facets.select_visibility',
        'browse_loading_visibilities' => 'ngrm.edit.vue.browse.facets.loading_visibilities',
        'browse_all_visibilities' => 'ngrm.edit.vue.browse.facets.all_visibilities',
        'search' => 'ngrm.edit.vue.browse.facets.search',
        'search_placeholder' => 'ngrm.edit.vue.browse.facets.search_placeholder',
        'crop_modal_title' => 'ngrm.edit.vue.crop.modal_title',
        'crop_reset' => 'ngrm.edit.vue.crop.reset',
        'crop_apply' => 'ngrm.edit.vue.crop.apply',
        'crop_cancel' => 'ngrm.edit.vue.crop.cancel',
        'crop_add' => 'ngrm.edit.vue.crop.add',
        'crop_preview' => 'ngrm.edit.vue.crop.preview',
        'crop_save_sizes' => 'ngrm.edit.vue.crop.save_sizes',
        'crop_add_size' => 'ngrm.edit.vue.crop.add_size',
        'crop_added_for_confirmation' => 'ngrm.edit.vue.crop.added_for_confirmation',
        'crop_media_too_small' => 'ngrm.edit.vue.crop.media_too_small',
        'media_gallery_empty_folder' => 'ngrm.edit.vue.media_gallery.empty_folder',
        'media_gallery_upload_media' => 'ngrm.edit.vue.media_gallery.upload_media',
        'media_gallery_select' => 'ngrm.edit.vue.media_gallery.select',
        'media_gallery_added' => 'ngrm.edit.vue.media_gallery.added',
        'media_gallery_load_more' => 'ngrm.edit.vue.media_gallery.load_more',
        'interactions_scale' => 'ngrm.edit.vue.interactions.scale',
        'interactions_no_media_selected' => 'ngrm.edit.vue.interactions.no_media_selected',
        'interactions_select_media' => 'ngrm.edit.vue.interactions.select_media',
        'interactions_manage_media' => 'ngrm.edit.vue.interactions.manage_media',
        'select_folder_interaction_button' => 'ngrm.edit.vue.select_folder_interaction.button',
        'select_folder_interaction_empty' => 'ngrm.edit.vue.select_folder_interaction.empty',
        'preview_alternate_text' => 'ngrm.edit.vue.preview.alternate_text',
        'preview_alternate_text_info' => 'ngrm.edit.vue.preview.alternate_text_info',
        'preview_caption' => 'ngrm.edit.vue.preview.caption',
        'preview_caption_info' => 'ngrm.edit.vue.preview.caption_info',
        'preview_alternate_text_override' => 'ngrm.edit.vue.preview.alternate_text_override',
        'preview_alternate_text_override_info' => 'ngrm.edit.vue.preview.alternate_text_override_info',
        'preview_caption_override' => 'ngrm.edit.vue.preview.caption_override',
        'preview_caption_override_info' => 'ngrm.edit.vue.preview.caption_override_info',
        'preview_tags' => 'ngrm.edit.vue.preview.tags',
        'preview_tags_info' => 'ngrm.edit.vue.preview.tags_info',
        'preview_watermark_text' => 'ngrm.edit.vue.preview.watermark_text',
        'preview_watermark_text_info' => 'ngrm.edit.vue.preview.watermark_text_info',
        'preview_css_class' => 'ngrm.edit.vue.preview.css_class',
        'preview_css_class_info' => 'ngrm.edit.vue.preview.css_class_info',
        'preview_selected_variation' => 'ngrm.edit.vue.preview.selected_variation',
        'preview_selected_variation_info' => 'ngrm.edit.vue.preview.selected_variation_info',
        'preview_size' => 'ngrm.edit.vue.preview.size',
        'upload_breadcrumbs_info' => 'ngrm.edit.vue.upload.breadcrumbs_info',
        'upload_root_folder' => 'ngrm.edit.vue.upload.root_folder',
        'upload_info_text' => 'ngrm.edit.vue.upload.info_text',
        'upload_create_folder_in' => 'ngrm.edit.vue.upload.create_folder_in',
        'upload_button_select' => 'ngrm.edit.vue.upload.button.select',
        'upload_button_create' => 'ngrm.edit.vue.upload.button.create',
        'upload_button_cancel' => 'ngrm.edit.vue.upload.button.cancel',
        'upload_checkbox_overwrite' => 'ngrm.edit.vue.upload.checkbox.overwrite',
        'upload_placeholder_new_folder' => 'ngrm.edit.vue.upload.placeholder.new_folder',
        'upload_error_existing_resource' => 'ngrm.edit.vue.upload.error.existing_resource',
        'upload_error_existing_resource_in_folder' => 'ngrm.edit.vue.upload.error.existing_resource_in_folder',
        'upload_error_unsupported_resource_type' => 'ngrm.edit.vue.upload.error.unsupported_resource_type',
        'upload_error_create_folder_failed' => 'ngrm.edit.vue.upload.error.create_folder_failed',
        'multi_upload_drop_zone' => 'ngrm.edit.vue.multi_upload.drop_zone',
        'multi_upload_drop_zone_single' => 'ngrm.edit.vue.multi_upload.drop_zone_single',
        'multi_upload_browse' => 'ngrm.edit.vue.multi_upload.browse',
        'multi_upload_browse_single' => 'ngrm.edit.vue.multi_upload.browse_single',
        'multi_upload_files_pending' => 'ngrm.edit.vue.multi_upload.files_pending',
        'multi_upload_files_success' => 'ngrm.edit.vue.multi_upload.files_success',
        'multi_upload_files_error' => 'ngrm.edit.vue.multi_upload.files_error',
        'multi_upload_uploading' => 'ngrm.edit.vue.multi_upload.uploading',
        'multi_upload_button_upload_all' => 'ngrm.edit.vue.multi_upload.button_upload_all',
        'multi_upload_clear_completed' => 'ngrm.edit.vue.multi_upload.clear_completed',
        'multi_upload_global_folder' => 'ngrm.edit.vue.multi_upload.global_folder',
        'multi_upload_global_visibility' => 'ngrm.edit.vue.multi_upload.global_visibility',
        'multi_upload_global_apply_to_all' => 'ngrm.edit.vue.multi_upload.global_apply_to_all',
        'multi_upload_table_preview' => 'ngrm.edit.vue.multi_upload.table.preview',
        'multi_upload_table_filename' => 'ngrm.edit.vue.multi_upload.table.filename',
        'multi_upload_table_folder' => 'ngrm.edit.vue.multi_upload.table.folder',
        'multi_upload_table_visibility' => 'ngrm.edit.vue.multi_upload.table.visibility',
        'multi_upload_table_status' => 'ngrm.edit.vue.multi_upload.table.status',
        'multi_upload_table_actions' => 'ngrm.edit.vue.multi_upload.table.actions',
        'multi_upload_table_upload_file' => 'ngrm.edit.vue.multi_upload.table.upload_file',
        'multi_upload_table_remove_file' => 'ngrm.edit.vue.multi_upload.table.remove_file',
        'multi_upload_use_existing' => 'ngrm.edit.vue.multi_upload.use_existing',
        'multi_upload_retry' => 'ngrm.edit.vue.multi_upload.retry',
        'multi_upload_duplicate_file' => 'ngrm.edit.vue.multi_upload.duplicate_file',
        'multi_upload_already_added' => 'ngrm.edit.vue.multi_upload.already_added',
        'multi_upload_submit_blocked_pending' => 'ngrm.edit.vue.multi_upload.submit_blocked.pending',
        'multi_upload_submit_blocked_uploading' => 'ngrm.edit.vue.multi_upload.submit_blocked.uploading',
        'multi_upload_error_empty_filename' => 'ngrm.edit.vue.multi_upload.error.empty_filename',
        'multi_upload_error_timeout' => 'ngrm.edit.vue.multi_upload.error.timeout',
        'multi_upload_error_failed' => 'ngrm.edit.vue.multi_upload.error.failed',
        'multi_upload_status_pending' => 'ngrm.edit.vue.multi_upload.status.pending',
        'multi_upload_status_success' => 'ngrm.edit.vue.multi_upload.status.success',
        'multi_upload_status_error' => 'ngrm.edit.vue.multi_upload.status.error',
        'multi_gallery_remove' => 'ngrm.edit.vue.multi_gallery_remove',
        'multi_select_add_selected' => 'ngrm.edit.vue.multi_select_add_selected',
        'multi_select_deselect' => 'ngrm.edit.vue.multi_select_deselect',
        'multi_select_selected' => 'ngrm.edit.vue.multi_select_selected',
        'multi_select_selected_remaining' => 'ngrm.edit.vue.multi_select_selected_remaining',
        'reorder_drag' => 'ngrm.edit.vue.reorder.drag',
        'reorder_move_previous' => 'ngrm.edit.vue.reorder.move_previous',
        'reorder_move_next' => 'ngrm.edit.vue.reorder.move_next',
        'limit_reached' => 'ngrm.edit.vue.limit_reached',
        'limit_exceeded' => 'ngrm.edit.vue.limit_exceeded',
        'limit_remaining' => 'ngrm.edit.vue.limit_remaining',
    ];

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {}

    /**
     * @return array<string, string>
     */
    public function translate(): array
    {
        $translations = [];
        foreach (self::MESSAGES as $key => $messageId) {
            $translations[$key] = $this->translator->trans($messageId, [], self::DOMAIN);
        }

        return $translations;
    }
}
