{def $croppable = ng_remote_croppable($attribute.object.class_identifier)}

{if $croppable}
    <input type="button" class="ngremotemedia-scale hid button" @click="handleCropClicked" value="{'Scale'|i18n( 'extension/ngremotemedia/interactions' )}" >
{/if}
