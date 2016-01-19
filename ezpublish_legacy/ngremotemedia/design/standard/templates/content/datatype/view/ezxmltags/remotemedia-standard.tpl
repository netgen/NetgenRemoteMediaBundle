<img src="{$image_url}"
     {if and(is_set($cssclass), is_string($cssclass), not($cssclass|compare('')))}class="{$cssclass|wash()}"{/if}
     {if and(is_set($style), is_string($style), not($style|compare('')))}style="{$style|wash()}"{/if}
     {if and(is_set($title), is_string($title), not($title|compare('')))}title="{$title|wash()}"{/if}
     {if and(is_set($alttext), is_string($alttext), not($alttext|compare('')))}alt="{$alttext|wash()}"{/if} />