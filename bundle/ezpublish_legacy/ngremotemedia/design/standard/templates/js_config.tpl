{symfony_include('@NetgenRemoteMedia/ezadminui/js_config.html.twig', hash(
    'content_type', $object.class_identifier
))}

{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
    {foreach $content_attributes_grouped as $attribute_identifier => $attribute}
        {if eq($attribute.data_type_string, 'ezxmltext')}
            <script type="text/javascript">
                RemoteMediaSelectedImage_{$attribute.id} = {ldelim}
                    id: "",
                    name: "",
                    type: "image",
                    mediaType: "image",
                    url: "",
                    browse_url: "",
                    alternateText: "",
                    tags: [],
                    size: "",
                    variations: {ldelim}{rdelim},
                    height: 0,
                    width: 0,
                {rdelim};

                RemoteMediaInputFields_{$attribute.id} = {ldelim}
                    'alt_text': 'alt_text_{$attribute.id}',
                    'image_variations': 'image_variations_{$attribute.id}',
                    'media_type': 'media_type_{$attribute.id}',
                    'new_file': 'new_file_{$attribute.id}',
                    'resource_id': 'resource_id_{$attribute.id}',
                    'tags': 'tags_{$attribute.id}[]',
                {rdelim};
            </script>

            <div class="ngremotemedia-type" data-id="{$attribute.id}" v-init:selected-image="RemoteMediaSelectedImage" v-init:config="RemoteMediaConfig">
                <editor-insert-modal
                    v-if="editorInsertModalOpen"
                    @close="handleEditorInsertModalClose"
                    :loading="editorInsertModalLoading"
                    field-id="{$attribute.id}"
                    content-type-identifier="{$object.class_identifier}"
                    :config="config"
                    :selected-image="selectedImage"
                    :selected-editor-variation="selectedEditorVariation"
                    :caption="caption"
                    :css-class="cssClass"
                ></editor-insert-modal>
            </div>
        {/if}
    {/foreach}
{/foreach}
