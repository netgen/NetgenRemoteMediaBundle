{if not(is_set($viewmode))}
    {def $viewmode = 'remotemedia-standard'}
{elseif and(
    is_string($viewmode),
    $viewmode|compare('')
)}
    {set $viewmode = 'remotemedia-standard'}
{/if}
{def $template = concat('design:content/datatype/view/ezxmltags/', $viewmode, '.tpl')}
{include uri=$template}
