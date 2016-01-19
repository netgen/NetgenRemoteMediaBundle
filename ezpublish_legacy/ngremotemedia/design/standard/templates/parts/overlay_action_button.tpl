<input type="button" class="remotemedia-scale hid button"
    data-truesize='{$media.size|json}'
    {if $handler.mediaFits}
    value="{'Scale'|i18n( 'content/edit' )}"
    {else}
    disabled="disabled"
    value="{'The uploaded image might be too small for this format'|i18n( 'content/edit' )}"
    {/if}
    data-versions='{$handler.toscale|json}'>
