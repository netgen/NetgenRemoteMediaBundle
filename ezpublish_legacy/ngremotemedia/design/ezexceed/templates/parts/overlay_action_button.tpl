{if or( is_set($handler.type)|not, eq($handler.type, 'image') )}
    <button type="button" class="btn btn-inverse scale edit-image"
        data-truesize='{$media.size|json}'
        data-versions='{$handler.toscale|json}'>
        <img class="hide" src="/extension/ezexceed/design/ezexceed/images/kp/16x16/white/Info.png" />
        {'Scale variants'|i18n('remotemedia')}
    </button>
{/if}
