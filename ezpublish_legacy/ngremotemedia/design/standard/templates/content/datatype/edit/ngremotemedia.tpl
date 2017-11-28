{def $base='ContentObjectAttribute'}

{if not(is_set($remote_value))}
    {def $remote_value = $attribute.content}
{/if}
{if not(is_set($fieldId))}
    {def $fieldId = $attribute.id}
{/if}
{if not(is_set($version))}
    {def $version=$attribute.version}
{/if}
{if not(is_set($contentObjectId))}
    {def $contentObjectId = $attribute.contentobject_id}
{/if}
{if not(is_set($availableFormats))}
    {def $availableFormats = ng_image_variations($attribute.object.class_identifier)}
{/if}

{if not(is_set($ajax))}
    {run-once}
    {include uri="design:parts/js_templates_1.tpl"}
    {/run-once}
{/if}

{if is_set($remote_value.metaData.resource_type)}
    {def $type = $remote_value.metaData.resource_type}
{else}
    {def $type = 'image'}
{/if}

{def $user=fetch( 'user', 'current_user' )}

{def $croppableVariations = ng_image_variations($attribute.object.class_identifier, true)}

<div class="ngremotemedia-type" data-available-variations={json_encode(scaling_format($croppableVariations))} data-user-id="{$user.contentobject_id}">
    {include uri="design:parts/ngremotemedia/preview.tpl"}
    {include uri="design:parts/ngremotemedia/interactions.tpl"}
</div>
