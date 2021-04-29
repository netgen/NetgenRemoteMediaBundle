{def $base='ContentObjectAttribute'}

{def $remote_value = $attribute.content}
{def $fieldId = $attribute.id}
{def $availableFormats = ng_image_variations($attribute.object.class_identifier)}

{def $croppableVariations = ng_image_variations($attribute.object.class_identifier, true)}
{def $editorVariations = ng_image_variations($attribute.object.class_identifier)}

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
        'type', $remote_value.resourceType,
        'paths', hash(
            'browse', "/ngremotemedia/browse"|ezurl('no', 'relative'),
            'load_facets', "/ngremotemedia/facets"|ezurl('no', 'relative'),
            'load_folders', "/ngremotemedia/subfolders"|ezurl('no', 'relative'),
            'create_folder', "/ngremotemedia/createfolder"|ezurl('no', 'relative')
        ),
        'available_variations', json_encode(scaling_format($croppableVariations)),
        'available_editor_variations', json_encode(list_format($editorVariations)),
        'fieldId', $fieldId,
        'input_fields', $inputFields
    )
)}

{def $user=fetch( 'user', 'current_user' )}

<div class="ngremotemedia-type" data-user-id="{$user.contentobject_id}" data-id="{$fieldId}" v-init:selected-image="RemoteMediaSelectedImage" v-init:config="RemoteMediaConfig">
    <interactions
        field-id="{$fieldId}"
        :config="config"
        :selected-image="selectedImage"
    ></interactions>
</div>
