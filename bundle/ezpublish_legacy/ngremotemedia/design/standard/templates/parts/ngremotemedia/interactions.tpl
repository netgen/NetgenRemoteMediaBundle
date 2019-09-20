<div id="ngremotemedia-buttons-{$fieldId}" class="ngremotemedia-buttons"
    data-id="{$fieldId}"
    data-contentobject-id="{$contentObjectId}"
    data-version="{$version}">

    <input type="hidden" name="{$base}_media_id_{$fieldId}" v-model="selectedImage.id" class="media-id" />

    {if $remote_value.resourceId}
        {if $type|eq('image')}
            {include uri="design:parts/scale_button.tpl"}
        {/if}
        <input type="button" @click="handleRemoveMediaClicked" class="ngremotemedia-remove-file button" value="{'Remove media'|i18n( 'extension/ngremotemedia/interactions' )}" />
    {/if}

    <input type="button" @click="handleBrowseMediaClicked" class="ngremotemedia-remote-file button" value="{'Manage media'|i18n( 'extension/ngremotemedia/interactions' )}" />

    <div class="ngremotemedia-local-file-container">
        <input type="button" class="btn btn-default ngremotemedia-local-file button upload-from-disk" value="{'Quick upload'|i18n( 'extension/ngremotemedia/interactions' )}">
    </div>
</div>
