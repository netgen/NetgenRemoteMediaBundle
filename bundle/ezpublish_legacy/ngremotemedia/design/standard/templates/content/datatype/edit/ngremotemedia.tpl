{def $base='ContentObjectAttribute'}
{def $remote_value = $attribute.content}
{def $field_id = $attribute.id}

{def $input_fields = hash(
    'alt_text', concat($base, '_alttext_', $field_id),
    'image_variations', concat($base, '_image_variations_', $field_id),
    'media_type', concat($base, '_media_type_', $field_id),
    'new_file', concat($base, '_new_file_', $field_id),
    'resource_id', concat($base, '_media_id_', $field_id),
    'tags', concat($base, '_tags_', $field_id, '[]')
)}

{symfony_include(
    '@NetgenRemoteMedia/ezadminui/parts/edit/ngrm_field.html.twig',
    hash(
        'content_type_identifier', $attribute.object.class_identifier,
        'field_id', $field_id,
        'field_value', $remote_value,
        'input_fields', $input_fields
    )
)}
