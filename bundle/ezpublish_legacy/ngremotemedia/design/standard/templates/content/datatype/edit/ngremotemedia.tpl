{def $base='ContentObjectAttribute'}

{def $remote_value = $attribute.content}
{def $fieldId = $attribute.id}

{def $inputFields = hash(
    'alt_text', concat($base, '_alttext_', $fieldId),
    'image_variations', concat($base, '_image_variations_', $fieldId),
    'media_type', concat($base, '_media_type_', $fieldId),
    'new_file', concat($base, '_new_file_', $fieldId),
    'resource_id', concat($base, '_media_id_', $fieldId),
    'tags', concat($base, '_tags_', $fieldId, '[]')
)}

{symfony_include(
    '@NetgenRemoteMedia/ezadminui/js_templates.html.twig',
    hash(
        'field_value', $remote_value,
        'fieldId', $fieldId,
        'input_fields', $inputFields
    )
)}

{def $user=fetch( 'user', 'current_user' )}

<div class="ngremotemedia-type" data-id="{$fieldId}" v-init:selected-image="RemoteMediaSelectedImage" v-init:config="RemoteMediaConfig">
    <interactions
        field-id="{$fieldId}"
        :config="config"
        :selected-image="selectedImage"
    ></interactions>
</div>
