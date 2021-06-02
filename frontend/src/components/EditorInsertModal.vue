<template>
  <modal :title="this.$root.$data.NgRemoteMediaTranslations.editor_insert_title" class="editor-insert-modal" @close="$emit('close')">
    <div :class="loading ? 'editor-insert-modal-body loading' : 'editor-insert-modal-body'">
      <interactions
        :field-id="fieldId"
        :config="config"
        :selected-image="selectedImage"
        @selectedImageChanged="handleSelectedImageChanged"
      ></interactions>

      <div class="form-field">
        <label :for="'selected_variation_'+fieldId">{{this.$root.$data.NgRemoteMediaTranslations.editor_insert_variations_label}}</label>
        <v-select
          :id="'selected_variation_'+fieldId"
          :options="config.availableEditorVariations"
          v-model="selectedEditorVariation"
          :placeholder="this.$root.$data.NgRemoteMediaTranslations.editor_insert_variations_original_image"
        />
      </div>

      <div class="form-field">
        <label :for="'caption_'+fieldId">Caption</label>
        <input type="text"
           :id="'caption_'+fieldId"
           name="Caption"
           v-model="caption"
           class="media-alttext data"
        >
      </div>

      <div class="form-field">
        <label :for="'css_class_'+fieldId">CSS class</label>
        <input type="text"
           :id="'css_class_'+fieldId"
           name="Caption"
           v-model="cssClass"
           class="media-alttext data"
        >
      </div>

      <i v-if="loading" class="ng-icon ng-spinner" />
    </div>
    <div class="action-strip">
      <button type="button" class="btn" @click="$emit('close')">{{this.$root.$data.NgRemoteMediaTranslations.editor_insert_cancel_button}}</button>
      <button type="button" class="btn btn-blue" @click="this.handleEditorInsertModalSave">
        <span>{{this.$root.$data.NgRemoteMediaTranslations.editor_insert_insert_button}}</span>
      </button>
    </div>
  </modal>
</template>

<script>
import Modal from "./Modal";
import Interactions from "./Interactions";
import vSelect from "vue-select";
import axios from "axios";

export default {
  name: "EditorInsertModal",
  props: ["loading", "fieldId", "contentTypeIdentifier", "config", "selectedImage", "selectedEditorVariation", "caption", "cssClass"],
  components: {
    'modal': Modal,
    'interactions': Interactions,
    "v-select": vSelect
  },
  methods: {
    async handleEditorInsertModalSave(){
      this.loading = true;

      var data = new FormData();
      data.append('resource_id', this.selectedImage.id);
      data.append('media_type', this.selectedImage.type);
      data.append('alt_text', this.selectedImage.alternateText);
      data.append('new_file', document.querySelector('input[name="'+this.$root.$data.RemoteMediaInputFields.new_file+'"]').files[0]);
      data.append('image_variations', JSON.stringify(this.selectedImage.variations));
      data.append('content_type_identifier', this.contentTypeIdentifier);
      data.append('variation', this.selectedEditorVariation);

      this.selectedImage.tags.forEach(tag => {
        data.append('tags[]', tag);
      });

      const response = await axios.post(this.$root.$data.config.paths.editor_insert, data);

      this.$root.$data.editorInsertCallback(response.data, this.caption, this.cssClass);
      this.$emit('close');
    },
    handleSelectedImageChanged(selectedImage) {
      this.selectedImage = selectedImage;
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import '../scss/variables';

.editor-insert-modal-body {
  padding: 20px;
  margin-bottom: 50px;
  overflow-y: auto;

  &.loading {
    opacity: 0.5;
  }

  .form-field + .form-field {
    margin-top: 15px;
  }

  .form-field {
    label {
      font-size: 14px;
      font-weight: normal;
      color: $boulder;
      margin-top: 5px;
      margin-bottom: 10px;
      display: block;
    }

    input {
      width: 100%;
      border: 1px solid $mercury;
      padding: 7px 10px;
      flex-grow: 1;
    }
  }
}

.action-strip {
  padding: 8px 15px;
  background-color: $white;
  text-align: right;
  -webkit-box-shadow: inset 1px 0 0 0 $mercury, 0 -1px 0 0 $mercury;
  box-shadow: inset 1px 0 0 0 $mercury, 0 -1px 0 0 $mercury;
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;

  button {
    margin-left: 10px;
  }

  .icon-floppy {
    margin-right: 5px;
  }
}
.ng-spinner {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);

  &:before {
    display: inline-block;
    animation: spinning 1500ms linear infinite;
  }
}
</style>
