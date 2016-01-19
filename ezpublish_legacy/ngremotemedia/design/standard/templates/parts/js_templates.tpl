{def $searchPlaceholder = '…'|i18n( 'content/remotemedia' )
    $search = 'Search for medias'|i18n( 'content/remotemedia' )
    $close = 'Close'|i18n( 'content/remotemedia' )
    $next = 'Next 25 &gt;'
    $prev = '&lt; Previous 25'
}

<script type="text/javascript">
{literal} RemoteMediaTranslations = { {/literal}
    searchPlaceholder : '{$searchPlaceholder}',
    search : '{$search}',
    close : '{$close}',
    next : '{$next}',
    prev : '{$prev}'
{literal} }; {/literal}
</script>

<script type="text/x-handlebars-template" id="tpl-remotemedia-browser">
{literal}
    <div id="remotemedia-browser">
        <div class="remotemedia header">
            <form onsubmit="javascript: return false;" class="search">
                <input type="text" name="remotemedia-search" placeholder="{{tr.searchPlaceholder}}" />
                <button type="button" class="search">{{tr.search}}</button>
                <a class="prev">{{tr.prev}}</a>
                <a class="next">{{tr.next}}</a>

                <button type="button" class="close">{{tr.close}}</button>
            </form>
        </div>

        <div class="remotemedia body">
        {{body}}
        </div>
    </div>
{/literal}
</script>

<script type="text/x-handlebars-template" id="tpl-remotemedia-item">
{literal}
    <div class="item" id="item-{{id}}">
        <a class="pick">
            <img src="{{thumb.url}}" />
            <span class="meta">{{filename}} ({{width}}x{{height}})</span>
            <span class="share{{#if shared}} shared{{/if}}"></span>
        </a>
    </div>
{/literal}
</script>

<script type="text/x-handlebars-template" id="tpl-remotemedia-scaledversion">
{literal}
    <a>
        <h2>{{name}}</h2><br />
        <p data-size=[{{size}}]>{{width}}x{{height}}</p>
        <span class="box"></span>
    </a>
    <div class="overlay"></div>
{/literal}
</script>

<script type="text/x-handlebars-template" id="tpl-remotemedia-scaler">
    {include uri="design:handlebars/stack/item/header.tpl" noWrap=true()}
{literal}
    <div class="stack-item-content">
        <div class="remotemedia" id="remotemedia-scaler">
            <div class="header">
                <ul></ul>
            </div>
            <div class="body">
                <div class="image-wrap">
                    <img src="{{media}}" />
                </div>
                <div id="remotemedia-scaler-controls">
                </div>
            </div>
        </div>
    </div>
{/literal}
</script>
