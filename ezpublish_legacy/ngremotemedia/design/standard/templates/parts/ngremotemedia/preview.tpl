{if $type|eq('image')}
    {def $media = ngremotemedia($value, '300x200', $availableFormats, true)}
    {def $thumb_url = $media.url}
{else}
    {def $thumb_url = videoThumbnail($value)}
{/if}

<div class="ngremotemedia-image">
    {if $value.resourceId}
        <div class="image-wrap">
            <img src="{$thumb_url}" />
        </div>

        <div class="image-meta">
            <h3 class="title">{$value.resourceId|wash}</h3>

            <div class="tagger">
                <div class="ngremotemedia-alttext">
                    <span class="help-block description">{'Alternate text'|i18n('ngremotemedia')}</span>
                    <input type="text"
                           name="{$base}_alttext_{$fieldId}" value="{$value.metaData.alt_text}" class="media-alttext data">
                </div>

                <div class="ngremotemedia-tags">
                    <div class="input-append add-tag">
                        <input type="text" class="tag no-autosave" placeholder="{'Add tag'|i18n( 'content/edit' )}" data-autosave="off">
                        <button class="btn tag" disabled type="button">{'Add tag'|i18n( 'content/edit' )}</button>
                    </div>
                    <div class="tags"></div>
                </div>

            </div>
            {if $value.size|null()|not()}
            <p>
            {'Size'|i18n( 'content/edit' )}: {$value.size|si( byte )}
            </p>
            {/if}
        </div>
    {/if}
</div>
