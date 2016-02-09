{def $contentClassAttribute = $attribute.contentclass_attribute}
{def $variations = $contentClassAttribute.data_text4}


{symfony_include(
    'NetgenRemoteMediaBundle:ezexceed/test:ngremotemedia.html.twig',
        hash(
            'value', $attribute.content,
            'fieldId', $attribute.id,
            'availableFormats', $variations
        )
)}
