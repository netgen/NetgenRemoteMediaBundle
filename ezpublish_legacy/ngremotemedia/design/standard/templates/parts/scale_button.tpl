{def $width = $remote_value.metaData.width}
{def $height = $remote_value.metaData.height}
{def $size =  array($width, $height)}
{def $croppableFormats = ng_image_variations($attribute.object.class_identifier, true)}
{def $croppable = ng_remote_croppable($attribute.object.class_identifier)}

{if $croppable}
<input type="button" class="ngremotemedia-scale hid button"
    data-truesize="{$size|json}"
    value="{'Scale'|i18n( 'extension/ngremotemedia/interactions' )}"
    data-variations={scaling_format($croppableFormats)|json}>
{/if}
g
