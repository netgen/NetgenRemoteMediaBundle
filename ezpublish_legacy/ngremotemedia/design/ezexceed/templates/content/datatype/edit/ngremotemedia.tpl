{def $contentClassAttribute = $attribute.contentclass_attribute}
{def $variations = $contentClassAttribute.data_text4}


{run-once}
  {ezscript( array(
      'remotemedia/shared/models.js',
      'remotemedia/shared/tagger.js'
  ))}
{/run-once}

{symfony_include(
    'NetgenRemoteMediaBundle:ezexceed/edit:ngremotemedia.html.twig',
        hash(
            'value', $attribute.content,
            'fieldId', $attribute.id,
            'availableFormats', $variations
        )
)}


