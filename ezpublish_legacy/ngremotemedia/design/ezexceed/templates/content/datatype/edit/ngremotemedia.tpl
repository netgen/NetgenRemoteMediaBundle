{def $contentClassAttribute = $attribute.contentclass_attribute}
{def $variations = $contentClassAttribute.data_text4}


{symfony_include(
    'NetgenRemoteMediaBundle:ezexceed/edit:ngremotemedia.html.twig',
        hash(
            'value', $attribute.content,
            'fieldId', $attribute.id,
            'availableFormats', $variations
        )
)}
