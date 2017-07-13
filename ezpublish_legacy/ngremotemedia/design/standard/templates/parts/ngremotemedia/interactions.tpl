<div id="ngremotemedia-buttons-{$fieldId}" class="ngremotemedia-buttons"
    data-id="{$fieldId}"
    data-contentobject-id="{$contentObjectId}"
    data-version="{$version}">

    <input type="hidden" name="{$base}_media_id_{$fieldId}" value="{$remote_value.resourceId}" class="media-id" />

    {if $remote_value.resourceId}
        {if $type|eq('image')}
            {include uri="design:parts/scale_button.tpl"}
        {/if}
        <input type="button" class="ngremotemedia-remove-file button" value="{'Remove media'|i18n( 'content/edit' )}">
    {/if}

    <input type="button" class="ngremotemedia-remote-file button" value="{'Choose from NgRemoteMedia'|i18n( 'content/edit' )}">

    <div class="ngremotemedia-local-file-container">
        <input type="button" class="ngremotemedia-local-file button upload-from-disk" value="{'Choose from computer'|i18n( 'content/edit' )}">
    </div>

    <div class="upload-progress hid" id="ngremotemedia-progress-{$fieldId}">
        <div class="progress"></div>
    </div>
</div>

{if is_content_browser_active()}
    <div class="test">
        <div class="js-input-browse item-empty"
             data-min_selected="1"
             data-max_selected="1"
             data-start_location="0"
        >
            <div class="input-browse">
                <span class="js-clear"><i class="material-icons">close</i></span>

                <a class="js-trigger" href="#">
                    <span class="js-name" data-empty-note="No item selected">{'Choose from NgRemoteMedia'|i18n( 'content/edit' )}</span>
                </a>
            </div>

            <input type="hidden" class="js-config-name" value="cloudinary" />
            <input type="hidden" class="js-value" id="CSS_ID" value="" />
        </div>
    </div>
{/if}

