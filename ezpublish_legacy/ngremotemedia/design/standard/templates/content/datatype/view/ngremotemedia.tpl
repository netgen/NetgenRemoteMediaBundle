{if is_set($format)|not}
    {def $format = '300x200'}
{/if}

{if is_set($silent)|not}
    {def $silent = true()}
{/if}

{def
    $value = $attribute.content
    $media = ngremotemedia($attribute, $format, true)
    $type = false()
}

{if $media.url|is_set()}

    {if $value.type|is_set()}
        {set $type = $value.type}
    {else}
        {set $type = 'image'}
    {/if}

    {*
    {def $template = concat('design:content/datatype/view/', $type, '.tpl')}

    {include uri=$template media=$media handler=$handler}
    *}

    <img src="{$media.url}" />

{else}
    {if $silent|not}{debug-log msg='Media.url not set'}{/if}
{/if}
