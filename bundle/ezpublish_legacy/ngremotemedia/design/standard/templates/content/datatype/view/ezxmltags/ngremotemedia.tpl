{if not(and(is_set($resourceType), is_string($resourceType), not($resourceType|compare(''))))}
    {def $resourceType = 'image'}
{/if}

{if and($resourceId, $resourceId|is_null|not)}
    {def $resource = ng_remote_resource($resourceType, $resourceId, $coords)}
{/if}

{if and(resource, $resource.resourceId|is_null|not)}
    <div{if and(is_set($align), is_string($align), not($align|compare('')))} class="object-{if $align|compare('middle')}center{else}{$align}{/if}"{/if}>
        {if eq($resource.mediaType, 'image')}
            {def $image_url = $resource.secure_url}

            {def $image_url = ngremotemedia($resource, 'embedded', $variation).url}

            <img src="{$image_url}"
                 {if and(is_set($cssclass), is_string($cssclass), not($cssclass|compare('')))}class="{$cssclass|wash()}"{/if}
                 {if and(is_set($style), is_string($style), not($style|compare('')))}style="{$style|wash()}"{/if}
                 {if and(is_set($resource.metaData.alt_text), is_string($resource.metaData.alt_text))}alt="{$resource.metaData.alt_text|wash()}"{/if} />
        {elseif eq($resource.mediaType, 'video')}
            {ngremotevideo($resource, $variation, 'embedded')}
        {else}
            <a href="{$resource.secure_url}">{$resourceId}</a>
        {/if}

        {if and(is_set($caption), is_string($caption), not($caption|compare('')))}<div class="img-caption">{$caption|wash()}</div>{/if}
    </div>
{/if}
