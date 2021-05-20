{if not(and(is_set($resourceType), is_string($resourceType), not($resourceType|compare(''))))}
    {def $resourceType = 'image'}
{/if}

{if and($resourceId, $resourceId|is_null|not)}
    {def $resource = ng_remote_resource($resourceType, $resourceId, $coords)}
{/if}

{if and(resource, $resource.resourceId|is_null|not)}
    {def $content_type_identifier = get_content_type_identifier()}

    {if eq($resourceType, 'image')}
        {def $image_url = $resource.secure_url}

        {if $content_type_identifier|is_null|not}
            {def $image_url = ngremotemedia($resource, $content_type_identifier, $variation).url}
        {/if}

        <img src="{$image_url}"
             {if and(is_set($cssclass), is_string($cssclass), not($cssclass|compare('')))}class="{$cssclass|wash()}"{/if}
             {if and(is_set($style), is_string($style), not($style|compare('')))}style="{$style|wash()}"{/if}
             {if and(is_set($resource.metaData.alt_text), is_string($resource.metaData.alt_text))}alt="{$resource.metaData.alt_text|wash()}"{/if} />
    {elseif eq($resourceType, 'video')}
        {ngremotevideo($resource, $variation, $content_type_identifier)}
    {else}
        <a href="{$resource.secure_url}">{$resourceId}</a>
    {/if}

    {if and(is_set($caption), is_string($caption), not($caption|compare('')))}<div class="img-caption">{$caption|wash()}</div>{/if}
{/if}
