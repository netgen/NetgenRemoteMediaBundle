{def $width = $value.metaData.width}
{def $height = $value.metaData.height}
{def $size =  array($width, $height)}

{def $mediaFits = mediaFits($value, $availableFormats)}

<input type="button" class="remotemedia-scale hid button"
    data-truesize='{$size|json}'
    {if $mediaFits}
    value="{'Scale'|i18n( 'content/edit' )}"
    {else}
    disabled="disabled"
    value="{'The uploaded image might be too small for this format'|i18n( 'content/edit' )}"
    {/if}
    data-versions='{$availableFormats|json}'>
