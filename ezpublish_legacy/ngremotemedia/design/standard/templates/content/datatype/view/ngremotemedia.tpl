{def $variations = ng_image_variations()}

{def
    $value = $attribute.content
    $type = $value.metaData.resource_type|default('image')
}

{if $value.resourceId}
    {if $type|eq('image')}
        {if not(is_set($format))}
            {def $format = '300x200'}
        {/if}

        {def $variation = ngremotemedia($value, $attribute.object.class_identifier, $format)}

        {if not(is_set($alt_text))}
            {def alt_text = $value.metaData.alt_text|default('')}
        {/if}

        {if not(is_set($title))}
            {def title = $value.metaData.caption|default('')}
        {/if}

        <img src="{$variation.url}"
            {if $variation.width} width="{$variation.width}"{/if}
            {if $variation.height} height="{$variation.height}"{/if}
             {if $alt_text}alt="{$alt_text}"{/if}
             {if $title}title="{$title}"{/if}
        />

    {elseif $type|eq('video')}
        {ngremotevideo($value, $variations, $format)}
    {/if}
{/if}
