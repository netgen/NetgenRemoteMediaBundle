{def $variations = ng_image_variations()}

{def
    $value = $attribute.content
}

{if $value.resourceId}
    {def $format = 'admin_preview'}
    {if $value.mediaType|eq('image')}
        {def $variation = ngremotemedia($value, $attribute.object.class_identifier, $format)}

        {if not(is_set($alt_text))}
            {def $alt_text = $value.metaData.alt_text|default('')}
        {/if}

        {if not(is_set($title))}
            {def $title = $value.metaData.caption|default('')}
        {/if}

        <img src="{$variation.url}"
            {if $variation.width} width="{$variation.width}"{/if}
            {if $variation.height} height="{$variation.height}"{/if}
             {if $alt_text}alt="{$alt_text}"{/if}
             {if $title}title="{$title}"{/if}
        />
    {elseif $value.mediaType|eq('video')}
        <i class="fa fa-video-camera" aria-hidden="true"></i>
        {$value.resourceId}

        <br/>

        {def $thumbnail = videoThumbnail($value, hash('content_type_identifier', $attribute.object.class_identifier, 'variation_name', $format))}
        <img src="{$thumbnail}"/>
    {else}
        <i class="fa fa-book" aria-hidden="true"></i>
        <a href="{$value.secure_url}" target="_blank" rel="noopener noreferrer">
            {$value.resourceId}
        </a>
    {/if}
{else}
    <i>No media selected</i>
{/if}
