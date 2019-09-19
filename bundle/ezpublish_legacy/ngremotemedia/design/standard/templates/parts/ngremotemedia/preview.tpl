<div class="ngremotemedia-image" v-init:selected-image="RemoteMediaSelectedImage">
    {if $remote_value.resourceId}
        <div class="image-wrap">
            <img v-if="selectedImage.type==='image'" :src="selectedImage.url"  />
            <i v-else="selectedImage.type!=='image'" :class="nonImagePreviewClass" class="ng-icon big"></i>
        </div>

        <div class="image-meta">
            {literal} <h3 class="title">{{selectedImage.name}}</h3> {/literal}

            <div class="tagger">
                <div class="ngremotemedia-alttext">
                    <span class="help-block description">{'Alternate text'|i18n('ngremotemedia')}</span>
                    <input type="text"
                            name="{$base}_alttext_{$fieldId}" v-model="selectedImage.alternateText" class="media-alttext data">
                </div>

                <select name="{$base}_tags_{$fieldId}[]" class="ngremotemedia-newtags" multiple="multiple">
                    {literal}<option v-for="tag in selectedImage.tags" :value="tag" selected="selected">{{tag}}</option>{/literal}
                </select>

            </div>
            {if $remote_value.size|null()|not()}
                <p>{'Size'|i18n( 'content/edit' )}: {literal}{{selectedImage.size}}{/literal}</p>
            {/if}
        </div>
    {/if}
</div>
