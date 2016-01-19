{if not( is_set( $handler ) ) }
    {def $handler = $attribute.content}
{/if}
{if not( is_set( $media ) ) }
    {def $media = $handler.media}
{/if}
{if eq(is_set($base), false())}
    {def $base='ContentObjectAttribute'}
{/if}
<input type="hidden" name="{$base}_media_id_{$attribute.id}" value="{$media.id}" class="media-id" />
<input type="hidden" name="{$base}_host_{$attribute.id}" value="{$media.host}" class="media-host" />
<input type="hidden" name="{$base}_type_{$attribute.id}" value="{$media.type}" class="media-type" />
<input type="hidden" name="{$base}_ending_{$attribute.id}" value="{$media.ending}" class="media-ending" />
{if $handler.backend}

    <div class="new-wrap"{if $media} style="display:none"{/if}>
        <span class="kp-icon50 pictures-icon"></span>

        <span class="upload-container" id="remotemedia-local-file-container-{$attribute.id}">
            <button type="button" class="upload" id="remotemedia-local-file-{$attribute.id}">{'Upload new media'|i18n('remotemedia')}</button>
        </span>
    </div>


    {if $media}
    <div class="meta">
        <p>
        {$media.name|wash}
        </p>
        <div class="tagger">
            <input type="text" class="tagedit" placeholder="{'Add a tag'|i18n('remotemedia')}" />
            <button type="button" class="tagit">
                {'Add tag'|i18n('remotemedia')}
            </button>
            <ul>
            </ul>
        </div>
    </div>
    {/if}

{else}
    <p class="error">{'No RemoteMedia connection for content class'|i18n('remotemedia')}</p>
{/if}
