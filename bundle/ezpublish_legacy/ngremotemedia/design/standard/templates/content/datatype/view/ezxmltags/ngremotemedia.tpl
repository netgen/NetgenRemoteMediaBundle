{def $resource = ng_remote_resource($resourceType, $resourceId)}

{if eq($resourceType, 'image')}
    <img src="{$resource.secure_url}"
         {if and(is_set($cssclass), is_string($cssclass), not($cssclass|compare('')))}class="{$cssclass|wash()}"{/if}
         {if and(is_set($style), is_string($style), not($style|compare('')))}style="{$style|wash()}"{/if}
         {if and(is_set($title), is_string($title), not($title|compare('')))}title="{$title|wash()}"{/if}
         {if and(is_set($alttext), is_string($alttext), not($alttext|compare('')))}alt="{$alttext|wash()}"{/if} />
{elseif eq($resourceType, 'video')}
    {def $thumbnail = videoThumbnail($resource)}

    <img src="{$thumbnail}" />
{else}
    <a href="{$resource.secure_url}">{$resourceId}</a>
{/if}
