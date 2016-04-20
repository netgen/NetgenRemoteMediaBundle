{def $contentClassAttribute = $attribute.contentclass_attribute}
{def $variations = $contentClassAttribute.content}

{def
    $value = $attribute.content
    $type = $value.metaData.resource_type|default('image')
}

{if $value.resourceId}
    {if $type|eq('image')}
        {if not(is_set($format))}
            {def $format = '300x200'}
        {/if}

        {if not(is_set($alt_text))}
            {def alt_text = $value.metaData.alt_text|default('')}
        {/if}

        {if not(is_set($title))}
            {def title = $value.metaData.caption|default('')}
        {/if}

        {if is_set($format)}
            {def $variation = ngremotemedia($value, $format, $variations)}
        {/if}

        {if is_set($variation)}
            <img src="{$variation.url}"
                {if $variation.width} width="{$variation.width}"{/if}
                {if $variation.height} height="{$variation.height}"{/if}
                 {if $alt_text}alt="{$alt_text}"{/if}
                 {if $title}title="{$title}"{/if}
            />
        {else}
            <img src="{$value.secure_url}"
                {if $value.metaData.width} width="{$value.metaData.width}"{/if}
                {if $value.metaData.height} height="{$value.metaData.height}"{/if}
                {if $alt_text}alt="{$alt_text}"{/if}
                {if $title}title="{$title}"{/if}
            />
        {/if}

    {elseif $type|eq('video')}
        {ngremotevideo($value, $variations, $format)}
    {/if}
{/if}
