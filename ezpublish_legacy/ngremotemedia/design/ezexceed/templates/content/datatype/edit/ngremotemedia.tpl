{def $contentClassAttribute = $attribute.contentclass_attribute}
{def $variations = $contentClassAttribute.data_text4}


{run-once}
  {ezscript( array(
      'ngremotemedia/shared/templates.js',
      'ngremotemedia/shared/models.js',
      'ngremotemedia/shared/tagger.js',
      'ngremotemedia/shared/browser.js',
      'ngremotemedia/shared/upload.js',
      'ngremotemedia/shared/scaled_version.js',
      'ngremotemedia/shared/scaler.js',
      'ngremotemedia/shared/ezoe.js'
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


