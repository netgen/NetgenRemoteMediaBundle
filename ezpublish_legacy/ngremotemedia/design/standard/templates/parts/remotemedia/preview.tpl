{if not(is_set($value)) }
    {def $value = $attribute.content}
{/if}
{if not(is_set($media))}
    {def $media = ngremotemedia($value, '300x200', true)}
{/if}

<div class="remotemedia-image">
    {if $media.url|eq('')|not}
        <div class="image-wrap">
                <img src="{$media.url}" />
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

