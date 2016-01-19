{if not( is_set( $handler ) ) }
    {def $handler = $attribute.content}
{/if}
{if not( is_set( $media ) ) }
    {def $media = $handler.media}
{/if}

<div class="remotemedia-image">
    <div class="image-wrap">
        {attribute_view_gui format=array($size,$size) attribute=$attribute}
    </div>

    {if $media}
    <div class="image-meta">
        <h3>{$media.name|wash}</h3>
        <div class="tagger">
            <h4>{'Tags'|i18n( 'content/edit' )}</h4>
            <input type="text" class="tagedit" />
            <input type="button" class="button tagit" id="remotemedia-tagger-{$attribute.id}"
                value="{'Add tag'|i18n( 'content/edit' )}">
            <ul>
            </ul>
        </div>
        <p>
        {'Modified'|i18n( 'content/edit' )}: {$media.modified|datetime('iso8601')},
        {'Size'|i18n( 'content/edit' )}: {$handler.filesize|si( byte )}
        </p>
    </div>
    {/if}
</div>
