{def $base='ContentObjectAttribute'}

{if not(is_set($value))}
    {def $value = $attribute.content}
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
{if not(is_set($variations))}
    {def $contentClassAttribute = $attribute.contentclass_attribute}
    {def $variations = $contentClassAttribute.data_text4}
{/if}

{if not(is_set($ajax))}
    {run-once}
    {include uri="design:parts/js_templates_1.tpl"}
    {/run-once}
{/if}

{if is_set($value.metaData.resource_type)}
    {def $type = $value.metaData.resource_type}
{else}
    {def $type = 'image'}
{/if}

{*$attribute.language_code*}

{def $user=fetch( 'user', 'current_user' )}

<div class="remotemedia-type" data-bootstrap-media={$value|json} data-user-id="{$user.contentobject_id}">
    {include uri="design:parts/remotemedia/preview.tpl"}
    {include uri="design:parts/remotemedia/interactions.tpl"}
</div>

