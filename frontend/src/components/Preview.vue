<template>
  <div v-if="selectedImage.id" class="ngremotemedia-image">
    <div class="image-wrap">
      <img v-if="selectedImage.type==='image' || selectedImage.type==='video'" :src="selectedImage.previewUrl" ref="image" />
      <span v-else class="icon-doc">
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
    </div>

    <div class="image-meta">
      <input type="hidden"
         :name="this.$root.$data.NgRemoteMediaInputFields.type"
         v-model="selectedImage.type"
      >

      <h3 class="title">{{selectedImage.name}}</h3>
      <p>{{this.$root.$data.NgRemoteMediaTranslations.preview_size}}: {{formattedSize}}</p>
      <p>{{selectedImage.type}} / {{selectedImage.format}}</p>

      <div class="image-meta-data">
        <div class="ngremotemedia-alttext">
          <span class="help-block description">{{this.$root.$data.NgRemoteMediaTranslations.preview_alternate_text}}</span>
          <input type="text"
               :name="this.$root.$data.NgRemoteMediaInputFields.altText"
               v-model="selectedImage.alternateText"
               class="media-alttext data"
          >
        </div>

        <div class="ngremotemedia-tags">
          <span class="help-block description">{{this.$root.$data.NgRemoteMediaTranslations.preview_tags}}</span>
          <v-select :options="$root.$data.NgRemoteMediaOptions.allowedTags.length > 0 ? $root.$data.NgRemoteMediaOptions.allowedTags : allTags" v-model="selectedImage.tags" multiple :taggable="$root.$data.NgRemoteMediaOptions.allowedTags.length === 0" @input="handleTagsInput"></v-select>
          <select hidden v-model="selectedImage.tags" :name="this.$root.$data.NgRemoteMediaInputFields.tags" class="ngremotemedia-newtags" multiple="multiple">
            <option v-for="tag in allTags" :key = "tag">{{tag}}</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div v-else>
    <i>{{this.$root.$data.NgRemoteMediaTranslations.interactions_no_media_selected}}</i>
  </div>
</template>

<script>
import { formatByteSize } from '../utility/utility';
import vSelect from "vue-select";

export default {
  name: "Preview",
  props: ["fieldId", "selectedImage"],
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
    },
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
