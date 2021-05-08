<template>
  <modal :title="this.$root.$data.NgRemoteMediaTranslations.editor_insert_title" class="editor-insert-modal" @close="$emit('close')">
    <div :class="loading ? 'editor-insert-modal-body loading' : 'editor-insert-modal-body'">
      <interactions
        :field-id="fieldId"
        :config="config"
        :selected-image="selectedImage"
        @selectedImageChanged="handleSelectedImageChanged"
      ></interactions>

      <v-select
        :options="config.availableEditorVariations"
        :label="Variation"
        @input="handleVariationChange"
        :placeholder="this.$root.$data.NgRemoteMediaTranslations.editor_insert_variations_original_image"
      />

      <input type="hidden" :name="this.$root.$data.RemoteMediaInputFields.content_type_identifier" :value="contentTypeIdentifier"/>
      <input type="hidden" :name="this.$root.$data.RemoteMediaInputFields.selected_variation"/>
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

export default {
  name: "EditorInsertModal",
  props: ["loading", "fieldId", "contentTypeIdentifier", "config", "selectedImage", "editorInsertAjaxUrl"],
  components: {
    'modal': Modal,
    'interactions': Interactions,
    "v-select": vSelect
  },
  methods: {
    handleVariationChange(value) {
      $('input[name="'+this.$root.$data.RemoteMediaInputFields.selected_variation+'"]').val(value);
    },
    handleEditorInsertModalSave(){
      this.loading = true;

      var data = new FormData();
      data.append('resource_id', $('body').find('input[name="'+this.$root.$data.RemoteMediaInputFields.resource_id+'"]').val());
      data.append('alt_text', $('body').find('input[name="'+this.$root.$data.RemoteMediaInputFields.alt_text+'"]').val());
      data.append('new_file', $('body').find('input[name="'+this.$root.$data.RemoteMediaInputFields.new_file+'"]')[0].files[0]);
      data.append('image_variations', $('body').find('input[name="'+this.$root.$data.RemoteMediaInputFields.image_variations+'"]').val());
      data.append('content_type_identifier', $('body').find('input[name="'+this.$root.$data.RemoteMediaInputFields.content_type_identifier+'"]').val());
      data.append('variation', $('body').find('input[name="'+this.$root.$data.RemoteMediaInputFields.selected_variation+'"]').val());

      var tagsArray = $('body').find('select[name="'+this.$root.$data.RemoteMediaInputFields.tags+'"]').val();

      if (!$.isArray(tagsArray)) {
        var tag = tagsArray;
        var tagsArray = [];

        if (tag) {
          tagsArray.push(tag);
        }
      }

      $.each(tagsArray, function (key, tag) {
        data.append('tags[]', tag);
      });

      var $this = this;
      $.ajax({
        type: 'post',
        url: this.editorInsertAjaxUrl,
        processData: false,
        contentType: false,
        data: data,
        success: function(data) {
          $this.$emit('media-inserted', data);
          $this.$emit('close');
          this.editorInsertModalLoading = false;
        }
      });
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
