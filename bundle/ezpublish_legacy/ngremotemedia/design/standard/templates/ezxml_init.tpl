{symfony_include('@NetgenRemoteMedia/ezadminui/js_config.html.twig')}

{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
    {foreach $content_attributes_grouped as $attribute_identifier => $attribute}
        {if eq($attribute.data_type_string, 'ezxmltext')}
            {symfony_include('@NetgenRemoteMedia/ezadminui/parts/edit/editor_insert.html.twig', hash(
                'field_id', $attribute.id
            ))}
        {/if}
    {/foreach}
{/foreach}
