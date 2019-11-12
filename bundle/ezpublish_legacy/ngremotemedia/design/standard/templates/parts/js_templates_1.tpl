<script type="text/javascript">
{literal} NgRemoteMediaTranslations = { {/literal}
    'Search for media': "{'Search for media'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Load more': "{'Load more'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Upload new media': "{'Upload new media'|i18n( 'extension/ngremotemedia/interactions' )}",
    'No results': "{'No results'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Alternate text': "{'Alternate text'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Class': "{'CSS class'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Create new folder?': "{'Create new folder?'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Folder': "{'Folder'|i18n( 'extension/ngremotemedia/interactions' )}",
    'All': "{'All'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Add tag': "{'Add tag'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Media type': "{'Media type'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Image': "{'Image and documents'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Video': "{'Video'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Loading...': "{'Loading...'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Cancel': "{'Cancel'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Save all': "{'Save all'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Generate': "{'Generate'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Caption': "{'Caption'|i18n( 'extension/ngremotemedia/interactions' )}",
    'by': "{'by'|i18n( 'extension/ngremotemedia/interactions' )}",
    'name': "{'name'|i18n( 'extension/ngremotemedia/interactions' )}",
    'tag': "{'tag'|i18n( 'extension/ngremotemedia/interactions' )}",
    'Image is to small for this version': "{'Image is to small for this version'|i18n( 'extension/ngremotemedia/interactions' )}",

    close : "{'Close'|i18n( 'extension/ngremotemedia/interactions' )}",
    next : "{'Next 25 &gt;'}",
    prev : "{'&lt; Previous 25'}"
{literal} }; {/literal}

{literal} RemoteMediaSettings = { {/literal}
    'ez_contentobject_id': {$contentObjectId},
    'ez_contentobject_version': {$version},
    'url_prefix': {'/'|ezurl}
    {literal} }; {/literal}

{if $type|eq('image')}
    {def $format = 'admin_preview'}
    {def $media = ngremotemedia($remote_value, $attribute.object.class_identifier, $format, true)}
    {def $thumb_url = $media.url}
{else}
    {def $thumb_url = videoThumbnail($remote_value)}
{/if}

{literal} RemoteMediaSelectedImage = { {/literal}
    {if $remote_value.mediaType|eq('image')}
        url: "{$thumb_url}",
        type: "image",
    {elseif $remote_value.mediaType|eq('video')}
        url: "{$thumb_url}",
        type: "video",
    {else}
        url: "{$remote_value.secure_url}",
        type: "other",
    {/if}
    name: "{$remote_value.resourceId|wash}",
    alternateText: "{$remote_value.metaData.alt_text}",
    {literal} tags: [ {/literal}
        {foreach $remote_value.metaData.tags as $tag}
            "{$tag}",
        {/foreach}
    {literal} ], {/literal}
    size: {if $remote_value.size|eq("")}0{else}{$remote_value.size}{/if},
    id: "{$remote_value.resourceId}",
    variations: {json_encode($remote_value.variations)},
    width: {if $remote_value.metaData.width|eq("")}0{else}{$remote_value.metaData.width}{/if},
    height: {if $remote_value.metaData.height|eq("")}0{else}{$remote_value.metaData.height}{/if}
{literal} }; {/literal}
</script>
