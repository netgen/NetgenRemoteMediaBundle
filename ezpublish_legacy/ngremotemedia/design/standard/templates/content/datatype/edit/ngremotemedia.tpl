{def $base='ContentObjectAttribute'
    $value = $attribute.content
    $media = ngremotemedia($value, '300x200', true)
}

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

{include uri="design:parts/js_templates.tpl"}
{/run-once}

<div class="remotemedia-type" data-bootstrap-media='{$value|json}'>
    {include uri="design:parts/remotemedia/preview.tpl" attribute=$attribute value=$value media=$media}
    {include uri="design:parts/remotemedia/interactions.tpl" attribute=$attribute base=$base value=$value media=$media}
</div>

