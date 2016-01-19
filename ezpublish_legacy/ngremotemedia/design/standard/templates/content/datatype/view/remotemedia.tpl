{if not( is_set( $format ) )}
    {def $format = array(300, 200)}
{/if}

{if is_set($quality)|not}
    {def $quality = false()}
{/if}

{if is_set($silent)|not}
    {def $silent = true()}
{/if}

{def
    $handler = $attribute.content
    $media = remotemedia($attribute, $format, $quality)
    $type = false()
}

{if eq($handler.id, 0)|not}
    {if $media.url|is_set()}
        {if $handler.type|is_set()}
            {set $type = $handler.type}
        {else}
            {set $type = $media.type}
        {/if}

        {if $silent|not}{debug-log msg='Loading type specific template:' var=$type}{/if}

        {def $template = concat('design:content/datatype/view/', $type, '.tpl')}
        {include uri=$template media=$media handler=$handler}
    {else}
        {if $silent|not}{debug-log msg='Media.url not set'}{/if}
    {/if}
{else}
    {if $silent|not}{debug-log msg='No media id connected to attribute'}{/if}
{/if}
