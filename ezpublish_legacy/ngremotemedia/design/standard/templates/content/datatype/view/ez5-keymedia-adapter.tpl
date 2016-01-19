{def
    $object = fetch('content', 'object', hash(
        'object_id', $objectId
    ))
}
{foreach $object.data_map as $attribute}
    {if and(
        $attribute.data_type_string|compare('ngremotemedia'),
        eq($attribute.id, $attributeId)
    )}
        {include uri='design:content/datatype/view/remotemedia.tpl'}
        {break}
    {/if}
{/foreach}
