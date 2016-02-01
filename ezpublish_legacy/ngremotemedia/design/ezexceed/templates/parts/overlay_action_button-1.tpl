{*if or( is_set($handler.type)|not, eq($handler.type, 'image') )*}

    {def $contentClassAttribute = $attribute.contentclass_attribute}
    {def $variations = $contentClassAttribute.data_text4}

    {def $width = $value.metaData.width}
    {def $height = $value.metaData.height}
    {def $size =  array($width, $height)}

    <button type="button" class="btn btn-inverse scale edit-image"
        data-truesize='{$size|json}'
        data-versions='{$variations}'>
        <img class="hide" src="/extension/ezexceed/design/ezexceed/images/kp/16x16/white/Info.png" />
        {'Scale variants'|i18n('remotemedia')}
    </button>
{*/if*}
