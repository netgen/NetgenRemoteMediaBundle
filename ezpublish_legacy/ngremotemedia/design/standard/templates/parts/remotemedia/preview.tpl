{if $type|eq('image')}
    {if not(is_set($media))}
        {def $media = ngremotemedia($value, '300x200', variations, true)}
    {/if}
    {def $thumb_url = $media.url}
{else}
    {def $thumb_url = videoThumbnail($value)}
{/if}

<div class="remotemedia-image">
    {if $value.resourceId}
        <div class="image-wrap">
                <img src="{$thumb_url}" />
        </div>

        <div class="image-meta">
            <h3>{$value.resourceId|wash}</h3>

            <div class="tagger">
                <h4>{'Tags'|i18n( 'content/edit' )}</h4>
                {if $value.metaData.tags|count()|gt(0)}
                    <ul>
                    {for $value.metaData.tags as $tag}
                        <li>{$tag}</li>
                    {/for}
                    </ul>
                {/if}

                <div class="remotemedia-alttext">
                    <span class="help-block description">{'Alternate text'|i18n('remotemedia')}</span>
                    <input type="text"
                           name="{$attribute_base}_alttext_{$fieldId}" value="{$value.metaData.alt_text}" class="media-alttext data">
                </div>


                <div class="remotemedia-tags">
                    <div class="input-append add-tag">
                        <input type="text" class="tag" placeholder="{'Add tag'|i18n( 'content/edit' )}" data-autosave="off">
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
