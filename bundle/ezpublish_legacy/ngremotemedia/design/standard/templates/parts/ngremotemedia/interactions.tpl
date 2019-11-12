<div id="ngremotemedia-buttons-{$fieldId}" class="ngremotemedia-buttons"
    data-id="{$fieldId}"
    data-contentobject-id="{$contentObjectId}"
    data-version="{$version}">

    <input type="hidden" name="{$base}_media_id_{$fieldId}" v-model="selectedImage.id" class="media-id" />

    {if $remote_value.resourceId}
        {def $croppable = ng_remote_croppable($attribute.object.class_identifier)}
        {if and( $type|eq('image'), $croppable )}
            <input type="button" class="ngremotemedia-scale hid button" @click="handleCropClicked" value="{'Scale'|i18n( 'extension/ngremotemedia/interactions' )}" >
        {/if}
        <input type="button" @click="handleRemoveMediaClicked" class="ngremotemedia-remove-file button" value="{'Remove media'|i18n( 'extension/ngremotemedia/interactions' )}" />
    {/if}

    <input type="button" @click="handleBrowseMediaClicked" class="ngremotemedia-remote-file button" value="{'Manage media'|i18n( 'extension/ngremotemedia/interactions' )}" />

    <div class="ngremotemedia-local-file-container">
        <button type="button" class="btn btn-default ngremotemedia-local-file button upload-from-disk">
            <Label for="new_file">
                {'Quick upload'|i18n( 'extension/ngremotemedia/interactions' )}
            </Label>
            <input hidden id="new_file" name="{$base}_new_file_{$fieldId}" type="file" @change="handleFileInputChange">
        </button>
    </div>
</div>
