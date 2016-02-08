{* {if not( is_set( $value ) ) }
    {def $value = $attribute.content}
{/if}

{if $value.metaData.resource_type|eq('image')}
    {def $media = ngremotemedia($attribute, '300x200', true)}

    {if $media}
        <div class="thumbnail">
            <img src="{$media.url}" />

            <button type="button" class="close remove"
                    name="CustomActionButton[{$attribute.id}_delete_media]"
                    value="{'Remove current media'|i18n('remotemedia')}">×</button>

            {include uri="design:parts/overlay_action_button-1.tpl" media=$media value=$value attribute=$attribute}
        </div>
    {/if}
{elseif $value.metaData.resource_type|eq('video')}
    {def $thumb = videoThumbnail($value)}

    <div class="thumbnail">
        <img src="{$thumb}" />

        <button type="button" class="close remove"
                name="CustomActionButton[{$attribute.id}_delete_media]"
                value="{'Remove current media'|i18n('remotemedia')}">×</button>
    </div>

{/if*}
