{def $value = $attribute.content}
{if is_set($format)|not}
    {def $format = '300x200'}
{/if}
{def $media = ngremotemedia($attribute, $format, true)}

<div class="remotemedia-image">
    <div class="image-wrap">
        <img src="{$media.url}" />
    </div>

    {if $media}
    <div class="image-meta">
        <h3>{$value.resourceId|wash}</h3>
        <div class="tagger">
            <h4>{'Tags'|i18n( 'content/edit' )}</h4>
            {if $value.metaData.tags|null()|not()}
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

