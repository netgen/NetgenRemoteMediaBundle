{def
    $handler = $attribute.content
    $media = $handler.media
}
{if is_set($attribute_base)|not}
    {def $attribute_base = "ContentObjectAttribute"}
{/if}

<div class="attribute-base"
    data-handler='remotemedia/remotemedia'
    data-url-root='{"/"|ezurl("no")}'
    {literal}
    data-paths='{
        "remotemedia" : "/extension/remotemedia/design/ezexceed/javascript/",
        "brightcove" : "/extension/remotemedia/design/standard/javascript/libs/BrightcoveExperiences"
    }'
    {/literal}
    data-bootstrap='{$media.data|json}'
>

{if $handler.backend|not}
    <p class="error">{'No RemoteMedia connection for content class'|i18n('remotemedia')}</p>
{else}
    {if and( $media, $handler.mediaFits|not )}
        <p class="error">{'The uploaded media might be too small for this format'|i18n('remotemedia')}</p>
    {/if}
{/if}

<input type="hidden" name="{$attribute_base}_media_id_{$attribute.id}" value="{$media.id}" class="media-id data"/>
<input type="hidden" name="{$attribute_base}_host_{$attribute.id}" value="{$media.host}" class="media-host data"/>
<input type="hidden" name="{$attribute_base}_type_{$attribute.id}" value="{$media.type}" class="media-type data"/>
<input type="hidden" name="{$attribute_base}_ending_{$attribute.id}" value="{$media.ending}" class="media-ending data"/>

{if $media}
    <div class="eze-image">
        {include uri="design:parts/remotemedia/preview.tpl"
            attribute=$attribute
            media=$media
            handler=$handler}

        <div class="remotemedia-alttext">
            <span class="help-block description">{'Alternate text'|i18n('remotemedia')}</span>
            <input type="text"
                   name="{$attribute_base}_alttext_{$attribute.id}" value="{$media.alttext}" class="media-alttext data">
        </div>
        <div class="remotemedia-tags">
            <div class="input-append add-tag">
                <input type="text" class="tag" placeholder="{'Write tag'|i18n('remotemedia')}" data-autosave="off">
                <button class="btn tag" disabled type="button">{'Add tag'|i18n('remotemedia')}</button>
            </div>
            <div class="tags"></div>
        </div>
    </div>
{/if}
<div class="eze-no-image">
    <button type="button" class="btn from-remotemedia">
        {'Browse media library'|i18n('remotemedia')}
    </button>
    <span class="upload-container" id="remotemedia-local-file-container-{$attribute.id}">
        <button type="button" class="btn upload-from-disk"
            id="remotemedia-local-file-{$attribute.id}">
            {'Upload new media'|i18n('remotemedia')}
        </button>
        <div class="upload-progress hide"><div class="progress"></div></div>
    </span>
</div>
</div>
