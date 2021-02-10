{def $base='ContentObjectAttribute'}

{def $remote_value = $attribute.content}
{def $fieldId = $attribute.id}
{def $version=$attribute.version}
{def $contentObjectId = $attribute.contentobject_id}
{def $availableFormats = ng_image_variations($attribute.object.class_identifier)}
{if is_set($remote_value.metaData.resource_type)}
    {def $type = $remote_value.metaData.resource_type}
{else}
    {def $type = 'image'}
{/if}

{def $croppableVariations = ng_image_variations($attribute.object.class_identifier, true)}
{def $editorVariations = ng_image_variations($attribute.object.class_identifier)}

{symfony_include(
    '@NetgenRemoteMedia/ezadminui/js_templates.html.twig',
    hash(
        'field_value', $remote_value,
        'type', $type,
        'paths', hash(
            'browse', "/ngremotemedia/browse"|ezurl('no', 'relative'),
            'folders', "/ngremotemedia/folders"|ezurl('no', 'relative')
        ),
        'available_variations', json_encode(scaling_format($croppableVariations)),
        'available_editor_variations', json_encode(list_format($editorVariations)),
        'fieldId', $fieldId
    )
)}

{def $user=fetch( 'user', 'current_user' )}

<div class="ngremotemedia-type" data-user-id="{$user.contentobject_id}" data-id="{$fieldId}" v-init:selected-image="RemoteMediaSelectedImage" v-init:config="RemoteMediaConfig">
    <interactions
        content-object-id="{$contentObjectId}"
        version="{$version}"
        field-id="{$fieldId}"
        base="{$base}"
        :config="config"
        :translations="NgRemoteMediaTranslations"
        :selected-image="selectedImage"
    ></interactions></div>
</div>
