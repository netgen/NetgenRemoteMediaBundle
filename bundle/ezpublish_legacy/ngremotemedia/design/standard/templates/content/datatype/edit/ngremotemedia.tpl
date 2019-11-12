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

{run-once}
{include uri="design:parts/js_templates_1.tpl"}
{/run-once}

{def $user=fetch( 'user', 'current_user' )}
{def $croppableVariations = ng_image_variations($attribute.object.class_identifier, true)}

<div class="ngremotemedia-type" data-user-id="{$user.contentobject_id}">

    {include uri="design:parts/ngremotemedia/preview.tpl"}

    {include uri="design:parts/ngremotemedia/interactions.tpl"}

    <input type="text" name="{$base}_image_variations_{$fieldId}" v-model="stringifiedVariations" class="media-id"/>
    <crop-modal v-if="cropModalOpen" @change="handleVariationCropChange" @close="handleCropModalClose" :selected-image="selectedImage" :available-variations={json_encode(scaling_format($croppableVariations))} data-user-id="{$user.contentobject_id}"></crop-modal>
    <media-modal :folders="folders" :selected-media-id="selectedImage.id" v-if="mediaModalOpen" @close="handleMediaModalClose" @media-selected="handleMediaSelected"/>
</div>
