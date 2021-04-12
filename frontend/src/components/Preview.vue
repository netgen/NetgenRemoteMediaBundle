<template>
  <div v-if="selectedImage.id" class="ngremotemedia-image">
    <div class="image-wrap">
      <img v-if="selectedImage.mediaType==='image'" :src="selectedImage.url" ref="image" />
      <i v-else="selectedImage.mediaType!=='image'" :class="nonImagePreviewClass" class="ng-icon big"></i>
    </div>

    <div class="image-meta">
      <input type="hidden"
         :name="this.$root.$data.RemoteMediaInputFields.media_type"
         v-model="selectedImage.type"
      >

      <h3 class="title">{{selectedImage.name}}</h3>
      <p>{{this.$root.$data.NgRemoteMediaTranslations.preview_size}}: {{formattedSize}}</p>

      <div class="image-meta-data">
        <div class="ngremotemedia-alttext">
          <span class="help-block description">{{this.$root.$data.NgRemoteMediaTranslations.preview_alternate_text}}</span>
          <input type="text"
               :name="this.$root.$data.RemoteMediaInputFields.alt_text"
               v-model="selectedImage.alternateText"
               class="media-alttext data"
          >
        </div>

        <div class="ngremotemedia-tags">
          <span class="help-block description">{{this.$root.$data.NgRemoteMediaTranslations.preview_tags}}</span>
          <v-select :options="allTags" v-model="selectedImage.tags" multiple taggable @input="handleTagsInput"></v-select>
          <select hidden v-model="selectedImage.tags" :name="this.$root.$data.RemoteMediaInputFields.tags" class="ngremotemedia-newtags" multiple="multiple">
            <option v-for="tag in allTags">{{tag}}</option>
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
