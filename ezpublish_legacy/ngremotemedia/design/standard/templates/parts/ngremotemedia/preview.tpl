{if $type|eq('image')}
    {def $format = hash('height', 400, 'width', 600)}
    {def $media = ngremotemedia($remote_value, $attribute.object.class_identifier, $format, true)}
    {def $thumb_url = $media.url}
{else}
    {def $thumb_url = videoThumbnail($remote_value)}
{/if}


<div class="ngremotemedia-image">
    {if $remote_value.resourceId}
        <div class="image-wrap">
            {if $remote_value.mediaType|eq('image')}
                <img src="{$thumb_url}" width="{$format.width}" height="{$format.height}" />
            {elseif $remote_value.mediaType|eq('video')}
                <img src="/extension/ngremotemedia/design/standard/images/video128x128.png" />
            {else}
                <img src="/extension/ngremotemedia/design/standard/images/book128x128.png" />
            {/if}
        </div>

        <div class="image-meta">
            <h3 class="title">{$remote_value.resourceId|wash}</h3>

            <div class="tagger">
                <div class="ngremotemedia-alttext">
                    <span class="help-block description">{'Alternate text'|i18n('ngremotemedia')}</span>
                    <input type="text"
                            name="{$base}_alttext_{$fieldId}" value="{$remote_value.metaData.alt_text}" class="media-alttext data">
                </div>

                <select class="ngremotemedia-newtags" multiple="multiple"></select>

            </div>
            {if $remote_value.size|null()|not()}
                <p>{'Size'|i18n( 'content/edit' )}: {$remote_value.size|si( byte )}</p>
            {/if}
        </div>
    {/if}
</div>
