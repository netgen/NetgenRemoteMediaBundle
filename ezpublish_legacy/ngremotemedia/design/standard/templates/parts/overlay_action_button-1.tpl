{def $width = $remote_value.metaData.width}
{def $height = $remote_value.metaData.height}
{def $size =  array($width, $height)}
{def $aliases = ng_image_variations()}
{def $croppableAliases = ng_image_variations(true)

{def $croppable = ng_remote_croppable($remote_value, $availableFormats)}

{if $croppable}
<input type="button" class="ngremotemedia-scale hid button"
    data-truesize="{$size|json}"
    value="{'Scale'|i18n( 'content/edit' )}"
    data-versions={$aliases|json}>
{/if}
