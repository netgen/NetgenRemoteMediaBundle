{if not(and(is_set($resourceType), is_string($resourceType), not($resourceType|compare(''))))}
    {def $resourceType = 'image'}
{/if}

{if and($resourceId, $resourceId|is_null|not)}
    {def $resource = ng_remote_resource($resourceType, $resourceId)}
{/if}

{if and(resource, $resource.resourceId|is_null|not)}
    {def $preview_image_url = $image_url}

    {if eq($resourceType, 'image')}
        {if $preview_image_url|compare('')}
            {$preview_image_url = $resource.secure_url}
        {/if}

        <img src="{$preview_image_url}"
             {if and(is_set($cssclass), is_string($cssclass), not($cssclass|compare('')))}class="{$cssclass|wash()}"{/if}
             {if and(is_set($style), is_string($style), not($style|compare('')))}style="{$style|wash()}"{/if}
             {if and(is_set($resource.metaData.alt_text), is_string($resource.metaData.alt_text))}alt="{$resource.metaData.alt_text|wash()}"{/if} />
    {elseif eq($resourceType, 'video')}
        {if $preview_image_url|compare('')}
            {$preview_image_url = videoThumbnail($resource)}
        {/if}

        <img src="{$preview_image_url}"/>
    {else}
        <a href="{$resource.secure_url}">{$resourceId}</a>
    {/if}
{/if}
