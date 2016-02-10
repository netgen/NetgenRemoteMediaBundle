{def $base='ContentObjectAttribute'}

{if not(is_set($value))}
    {def $value = $attribute.content}
{/if}
{if not(is_set($media))}
    {def $media = ngremotemedia($value, '300x200', true)}
{/if}
{if not(is_set($fieldId))}
    {def $fieldId = $attribute.id}
{/if}
{if not(is_set($version))}
    {def $version=$attribute.version}
{/if}
{if not(is_set($contentObjectId))}
    {def $contentObjectId = $attribute.contentobject_id}
{/if}
{if not(is_set($variations))}
    {def $contentClassAttribute = $attribute.contentclass_attribute}
    {def $availableFormats = $contentClassAttribute.data_text4}
{/if}

{run-once}
{foreach ezcssfiles(array('jquery.jcrop.css', 'ngremotemedia.css')) as $file}
<link rel="stylesheet" type="text/css" href="{$file}?v2.0.0" />
{/foreach}
{ezscript_require( array(
    'ezjsc::jquery',
    'libs/lodash.js',
    'libs/backbone.js'
))}
{ezscript( array(
    'libs/handlebars.js',
    'libs/plupload/moxie.js',
    'libs/plupload/plupload.js',
    'libs/jquery.jcrop.min.js',

    'remotemedia/ns.js',
    'remotemedia/Attribute.js',
    'remotemedia/Media.js',

    'remotemedia/views/Modal.js',
    'remotemedia/views/RemoteMedia.js',
    'remotemedia/views/Scalebox.js',
    'remotemedia/views/Scaler.js',
    'remotemedia/views/Browser.js',
    'remotemedia/views/Upload.js',
    'remotemedia/views/Tagger.js',
    'remotemedia/views/EzOE.js',

    'remotemedia/run.js'
) )}

{include uri="design:parts/js_templates_1.tpl"}
{/run-once}

{if is_set($value.metaData.resource_type)}
    {def $type = $value.metaData.resource_type}
{else}
    {def $type = 'image'}
{/if}

<div class="remotemedia-type" data-bootstrap-media='{$value|json}'>
    {include uri="design:parts/remotemedia/preview.tpl"}
    {include uri="design:parts/remotemedia/interactions.tpl"}
</div>

