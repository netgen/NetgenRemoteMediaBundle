{if not(is_set($value)) }
    {def $value = $attribute.content}
{/if}

{if $type|eq('image')}
    {if not(is_set($media))}
        {def $media = ngremotemedia($value, '300x200', true)}
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
                           name="{$attribute_base}_alttext_{$attribute.id}" value="{$value.metaData.alt_text}" class="media-alttext data">
                </div>

                <input type="text" class="tagedit" />
                <input type="button" class="button tagit" id="remotemedia-tagger-{$attribute.id}"
                    value="{'Add tag'|i18n( 'content/edit' )}">
                <ul>
                </ul>
            </div>
            {if $value.size|null()|not()}
            <p>
            {'Size'|i18n( 'content/edit' )}: {$value.size|si( byte )}
            </p>
            {/if}
        </div>
    {/if}
</div>

