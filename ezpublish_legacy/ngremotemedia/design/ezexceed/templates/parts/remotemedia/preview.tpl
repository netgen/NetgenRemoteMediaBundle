{if not( is_set( $value ) ) }
    {def $value = $attribute.content}
{/if}
{if not( is_set( $media ) ) }
    {def $media = ngremotemedia($attribute, '300x200', true)}
{/if}

{if $media}
    <div class="thumbnail">
        <img src="{$media.url}" />

        {if eq($attribute.content.id, 0)|not}
        <button type="button" class="close remove"
                name="CustomActionButton[{$attribute.id}_delete_media]"
                value="{'Remove current media'|i18n('remotemedia')}">×</button>
        {/if}

        {include uri="design:parts/overlay_action_button.tpl" media=$media value=$value}
    </div>
{/if}
