<div class="ngremotemedia-image" v-init:selected-image="RemoteMediaSelectedImage">
        <div class="image-wrap">
            <img v-if="selectedImage.type==='image'" :src="selectedImage.url" ref="image" />
            <i v-else="selectedImage.type!=='image'" :class="nonImagePreviewClass" class="ng-icon big"></i>
        </div>

        <div class="image-meta">
            {literal} <h3 class="title">{{selectedImage.name}}</h3> {/literal}

            <div class="image-meta-data">
                <div class="ngremotemedia-alttext">
                    <span class="help-block description">{'Alternate text'|i18n('ngremotemedia')}</span>
                    <input type="text"
                            name="{$base}_alttext_{$fieldId}" v-model="selectedImage.alternateText" class="media-alttext data">
                </div>

                <v-select :options="allTags" v-model="selectedImage.tags" multiple taggable @input="handleTagsInput"></v-select>
                <select hidden v-model="selectedImage.tags" name="{$base}_tags_{$fieldId}[]" class="ngremotemedia-newtags" multiple="multiple">
                    {literal}<option v-for="tag in allTags">{{tag}}</option>{/literal}
                </select>

            </div>
            <p>{'Size'|i18n( 'content/edit' )}: {literal}{{formattedSize}}{/literal}</p>
        </div>    
</div>
