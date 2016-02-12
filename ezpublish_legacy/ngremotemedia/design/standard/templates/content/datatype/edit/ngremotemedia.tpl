{def $base='ContentObjectAttribute'}

{if not(is_set($value))}
    {def $value = $attribute.content}
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
    {def $variations = $contentClassAttribute.data_text4}
{/if}

{if not(is_set($ajax))}
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
        'libs/handlebars.runtime.js',
        'libs/plupload/moxie.js',
        'libs/plupload/plupload.js',
        'libs/jquery.jcrop.min.js',

        'remotemedia/ns.js',

        'remotemedia/shared/templates.js',
        'remotemedia/shared/models.js',
        'remotemedia/shared/tagger.js',
        'remotemedia/shared/browser.js',
        'remotemedia/shared/upload.js',

        'remotemedia/models.js',

        'remotemedia/views/Modal.js',
        'remotemedia/views/RemoteMedia.js',
        'remotemedia/views/Scalebox.js',
        'remotemedia/views/Scaler.js',
        'remotemedia/views/Upload.js',
        'remotemedia/views/Browser.js',
        'remotemedia/views/Tagger.js',
        'remotemedia/views/EzOE.js',

        'remotemedia/run.js'
    ) )}

    {include uri="design:parts/js_templates_1.tpl"}
    {/run-once}
{/if}

{if is_set($value.metaData.resource_type)}
    {def $type = $value.metaData.resource_type}
{else}
    {def $type = 'image'}
{/if}

{*$attribute.language_code*}

{def $user=fetch( 'user', 'current_user' )}

<div class="remotemedia-type" data-bootstrap-media='{$value|json}' data-user-id="{$user.contentobject_id}">
    {include uri="design:parts/remotemedia/preview.tpl"}
    {include uri="design:parts/remotemedia/interactions.tpl"}
</div>

