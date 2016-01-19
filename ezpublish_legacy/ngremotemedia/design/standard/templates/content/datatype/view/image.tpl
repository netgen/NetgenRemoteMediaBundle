{if is_set($focalPoint)|not}
    {def $focalPoint = true()}
{/if}
{if $format|is_array|not}
    {set $focalPoint = true()}
{/if}
<img src="{$media.url}{if $focalPoint|not}?original=1{/if}"
    {if is_set($class)}class="{$class}"{/if}
    {if is_set($width)}width="{$width}"{/if}
    {if is_set($height)}height="{$height}"{/if}
    {if is_set($title)}title="{$title}"{/if}
    {if is_set($alt)}alt="{$alt}"{/if} />
