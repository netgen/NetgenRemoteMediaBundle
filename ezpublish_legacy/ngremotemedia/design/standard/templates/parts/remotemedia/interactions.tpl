{if $handler.backend}
    {if eq(is_set($base), false())}
        {def $base='ContentObjectAttribute'}
    {/if}
<div id="remotemedia-buttons-{$attribute.id}" class="remotemedia-buttons"
    data-prefix={'/ezjscore/call'|ezurl}
    data-id="{$attribute.id}"
    data-contentobject-id="{$attribute.contentobject_id}"
    data-version="{$attribute.version}">

    <input type="hidden" name="{$base}_media_id_{$attribute.id}" value="{$media.id}" class="media-id" />
    <input type="hidden" name="{$base}_host_{$attribute.id}" value="{$media.host}" class="media-host" />
    <input type="hidden" name="{$base}_type_{$attribute.id}" value="{$media.type}" class="media-type" />
    <input type="hidden" name="{$base}_ending_{$attribute.id}" value="{$media.ending}" class="media-ending" />

    {if $media}
        {include uri="design:parts/overlay_action_button.tpl"
            media=$media handler=$handler}
        <input type="button" class="remotemedia-remove-file button" value="{'Remove media'|i18n( 'content/edit' )}">
    {/if}

    <input type="button" class="remotemedia-remote-file button" value="{'Choose from RemoteMedia'|i18n( 'content/edit' )}">

    <div class="remotemedia-local-file-container" id="remotemedia-local-file-container-{$attribute.id}">
        <input type="button" class="remotemedia-local-file button" id="remotemedia-local-file-{$attribute.id}"
            value="{'Choose from computer'|i18n( 'content/edit' )}">
    </div>

    <div class="upload-progress hid" id="remotemedia-progress-{$attribute.id}">
        <div class="progress"></div>
    </div>
</div>
{else}
<h2 class="error">{'No RemoteMedia connection for content class'|i18n( 'remotemedia' )}</h2>
{/if}
