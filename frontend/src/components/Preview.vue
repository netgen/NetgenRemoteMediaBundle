<template>
  <div class="ngremotemedia-image">
    <div class="image-wrap">
      <img v-if="selectedImage.type==='image'" :src="selectedImage.url" ref="image" />
      <i v-else="selectedImage.type!=='image'" :class="nonImagePreviewClass" class="ng-icon big"></i>
    </div>

    <div class="image-meta">
      <h3 class="title">{{selectedImage.name}}</h3>

      <div class="image-meta-data">
        <div class="ngremotemedia-alttext">
          <span class="help-block description">{{this.$root.$data.NgRemoteMediaTranslations.preview_alternate_text}}</span>
          <input type="text"
               :name="base+'_alttext_'+fieldId"
               v-model="selectedImage.alternateText"
               class="media-alttext data"
          >
        </div>

        <v-select :options="allTags" v-model="selectedImage.tags" multiple taggable @input="handleTagsInput"></v-select>
        <select hidden v-model="selectedImage.tags" :name="base+'_tags_'+fieldId+'[]'" class="ngremotemedia-newtags" multiple="multiple">
          <option v-for="tag in allTags">{{tag}}</option>
        </select>
      </div>
      <p>{{this.$root.$data.NgRemoteMediaTranslations.preview_size}}: {{formattedSize}}</p>
    </div>
  </div>
</template>

<script>
import { formatByteSize } from '../utility/utility';
import vSelect from "vue-select";

export default {
  name: "Preview",
  props: ["fieldId", "base", "selectedImage", "altText"],
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
  }
}
</script>
