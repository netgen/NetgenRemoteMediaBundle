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

    {def $remote_folders = remote_folders()}
    {if $remote_folders|count|gt(0)}
        <div class="ngremotemedia-folder-selection">
            <select>
                <option value="root">{'Root folder'|i18n( 'content/edit' )}</option>
                {foreach $remote_folders as $folder}
                    <option value="{$folder.path}">{$folder.name}</option>
                {/foreach}
            </select>
        </div>
    {/if}

    <div class="upload-progress hid" id="ngremotemedia-progress-{$fieldId}">
        <div class="progress"></div>
    </div>
</div>
