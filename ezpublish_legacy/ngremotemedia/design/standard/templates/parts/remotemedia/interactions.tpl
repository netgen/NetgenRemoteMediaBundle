<div id="remotemedia-buttons-{$fieldId}" class="remotemedia-buttons"
    data-prefix={'/ezjscore/call'|ezurl}
    data-id="{$fieldId}"
    data-contentobject-id="{$contentObjectId}"
    data-version="{$version}">

    <input type="hidden" name="{$base}_media_id_{$fieldId}" value="{$value.resourceId}" class="media-id" />

    {if $value.resourceId}
        {if $type|eq('image')}
            {include uri="design:parts/overlay_action_button-1.tpl"}
        {/if}
        <input type="button" class="remotemedia-remove-file button" value="{'Remove media'|i18n( 'content/edit' )}">
    {/if}

    <input type="button" class="remotemedia-remote-file button" value="{'Choose from RemoteMedia'|i18n( 'content/edit' )}">

    <div class="remotemedia-local-file-container" id="remotemedia-local-file-container-{$fieldId}">
        <input type="button" class="remotemedia-local-file button" id="remotemedia-local-file-{$fieldId}"
            value="{'Choose from computer'|i18n( 'content/edit' )}">
    </div>

    <div class="upload-progress hid" id="remotemedia-progress-{$fieldId}">
        <div class="progress"></div>
    </div>
</div>
