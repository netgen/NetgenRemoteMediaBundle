<div class="block">
    <h6>{'Versions'|i18n( 'design/standard/class/datatype' )}:</h6>
    <ul>
        {foreach $class_attribute.content as $name => $format}
            <li>{$name},{$format}</li>
        {/for}
    </ul>
</div>
