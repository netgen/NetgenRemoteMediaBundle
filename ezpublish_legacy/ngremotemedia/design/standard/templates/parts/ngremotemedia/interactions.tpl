<div id="ngremotemedia-buttons-{$fieldId}" class="ngremotemedia-buttons"
    data-id="{$fieldId}"
    data-contentobject-id="{$contentObjectId}"
    data-version="{$version}">

    <input type="hidden" name="{$base}_media_id_{$fieldId}" value="{$remote_value.resourceId}" class="media-id" />

    {if $remote_value.resourceId}
        {if $type|eq('image')}
            {include uri="design:parts/scale_button.tpl"}
        {/if}
        <input type="button" class="ngremotemedia-remove-file button" value="{'Remove media'|i18n( 'content/edit' )}" />
    {/if}

    <input type="button" class="ngremotemedia-remote-file button" value="{'Choose from NgRemoteMedia'|i18n( 'content/edit' )}" />

    <div class="ngremotemedia-local-file-container">
        <button class="btn btn-default ngremotemedia-local-file button upload-from-disk">{'Choose from computer'|i18n( 'content/edit' )}</button>
    </div>
</div>
