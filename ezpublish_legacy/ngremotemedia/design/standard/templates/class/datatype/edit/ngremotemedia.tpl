{def $base = 'ContentClass'}

{def $output=''}

{foreach $class_attribute.content as $name => $format}
    {set $output = concat($output, $name, ',', $format, '\n')}
{/foreach}

<div class="block">
    <label>{'Available formats'|i18n( 'design/standard/class/datatype/ngremotemedia' )}:</label>
    <textarea rows="5" cols="45" name="{$base}_versions_{$class_attribute.id}">{$output|trim()}</textarea>
    <p>
        {'1 row = 1 version: eg: Square,500x500'|i18n( 'design/standard/class/datatype/ngremotemedia' )}
    </p>
</div>
