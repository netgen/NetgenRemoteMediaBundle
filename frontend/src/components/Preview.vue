<template>
  <div v-if="selectedImage.id" class="ngremotemedia-image">
    <div class="image-wrap">
      <img v-if="selectedImage.type==='image' || selectedImage.type==='video'" :src="selectedImage.previewUrl" ref="image" />

      <span v-else class="file-placeholder">
        <span class="icon-doc">
          <i v-if="selectedImage.format==='pdf'" class="fa fa-file-pdf-o"></i>
          <i v-else-if="selectedImage.format==='zip' || selectedImage.format==='rar'" class="fa fa-file-archive-o"></i>
          <i v-else-if="selectedImage.format==='ppt' || selectedImage.format==='pptx'" class="fa fa-file-powerpoint-o"></i>
          <i v-else-if="selectedImage.format==='doc' || selectedImage.format==='docx'" class="fa fa-file-word-o"></i>
          <i v-else-if="selectedImage.format==='xls' || selectedImage.format==='xlsx'" class="fa fa-file-excel-o"></i>
          <i v-else-if="selectedImage.format==='aac' || selectedImage.format==='aiff' || selectedImage.format==='amr' || selectedImage.format==='flac'|| selectedImage.format==='m4a'
                    || selectedImage.format==='mp3' || selectedImage.format==='ogg' || selectedImage.format==='opus' || selectedImage.format==='wav'" class="fa fa-file-audio-o"></i>
          <i v-else-if="selectedImage.format==='txt'" class="fa fa-lg fa-file-text"></i>
          <i v-else class="fa fa-file"></i>
        </span>
      </span>
    </div>

    <div class="image-meta">
      <input type="hidden"
         :name="this.config.inputFields.type"
         v-model="selectedImage.type"
      >

      <h3 class="title">{{selectedImage.name}}</h3>
      <p>{{this.config.translations.preview_size}}: {{formattedSize}}</p>
      <p>{{selectedImage.type}} / {{selectedImage.format}}</p>

      <div class="image-meta-data">
        <div class="ngremotemedia-alttext">
          <span class="help-block description">{{this.config.translations.preview_alternate_text}}</span>
          <input type="text"
               :name="this.config.inputFields.altText"
               v-model="selectedImage.alternateText"
               @input="dispatchChangeEvent"
               class="media-alttext data"
          >
        </div>

        <div class="ngremotemedia-caption">
          <span class="help-block description">{{this.config.translations.preview_caption}}</span>
          <input type="text"
               :name="this.config.inputFields.caption"
               v-model="selectedImage.caption"
               class="media-caption data"
          >
        </div>

        <div class="ngremotemedia-tags">
          <span class="help-block description">{{this.config.translations.preview_tags}}</span>
          <v-select :options="config.allowedTags.length > 0 ? config.allowedTags : allTags" v-model="selectedImage.tags" multiple :taggable="config.allowedTags.length === 0" @input="handleTagsInput"></v-select>
          <select hidden v-model="selectedImage.tags" :name="this.config.inputFields.tags" class="ngremotemedia-newtags" multiple="multiple">
            <option v-for="tag in allTags" :key = "tag">{{tag}}</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div v-else>
    <i>{{this.config.translations.interactions_no_media_selected}}</i>
  </div>
</template>

<script>
import { formatByteSize } from '../utility/utility';
import vSelect from "vue-select";

export default {
  name: "Preview",
  props: ["config", "fieldId", "selectedImage"],
  data() {
    return {
      allTags: []
    };
  },
  components: {
    'v-select': vSelect,
  },
  computed: {
    nonImagePreviewClass() {
      return this.selectedImage.type === 'video' ? 'ng-video' : 'ng-book';
    },
    formattedSize() {
      return formatByteSize(this.selectedImage.size);
    },
  },
  methods: {
    handleTagsInput(value) {
      this.allTags = [...new Set([...this.allTags, ...value])];
      this.dispatchChangeEvent();
    },
    dispatchChangeEvent() {
      this.$emit('preview-change');
    }
  },
  mounted() {
    this.allTags = [...this.selectedImage.tags];
  },
  watch: {
    selectedImage: function() {
      this.allTags = [...this.selectedImage.tags];
    }
  }
}
</script>


<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

.ngremotemedia-image {
  .image-wrap {
    .file-placeholder {
      position: relative;
      max-width: 500px;
      height: 280px;
      display: block;
      margin-bottom: 4px;

      .icon-doc {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: $white;
        font-size: 40px;
      }

      &:before {
        position: absolute;
        content: '';
      }

      &:before {
        background-color: rgba(0, 0, 0, .7);
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
      }
    }
  }
}
</style>
