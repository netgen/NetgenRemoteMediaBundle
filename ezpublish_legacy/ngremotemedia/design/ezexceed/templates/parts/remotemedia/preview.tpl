{if not( is_set( $handler ) ) }
    {def $handler = $attribute.content}
{/if}
{if not( is_set( $media ) ) }
    {def $media = $handler.media}
{/if}

{if $media}
    <div class="thumbnail">
        {attribute_view_gui
            format=array(300,100)
            attribute=$attribute
            fetchinfo=true()
            nojs=true()
        }

        {if eq($attribute.content.id, 0)|not}
        <button type="button" class="close remove"
                name="CustomActionButton[{$attribute.id}_delete_media]"
                value="{'Remove current media'|i18n('remotemedia')}">×</button>
        {/if}

        {include uri="design:parts/overlay_action_button.tpl" media=$media handler=$handler}
    </div>
{/if}
