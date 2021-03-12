<template>
  <modal :title="this.$root.$data.NgRemoteMediaTranslations.editor_insert_title" class="editor-insert-modal" @close="$emit('close')">
    <div v-if="!editorInsertModalLoading" class="editor-insert-modal-body">
      <interactions
        :content-object-id="contentObjectId"
        :version="contentVersion"
        :field-id="fieldId"
        :base="base"
        :config="config"
        :translations="translations"
        :selected-image="selectedImage"
      ></interactions>

      <v-select
        :options="this.config.availableEditorVariations"
        :label="Variation"
        @input="handleVariationChange"
        :placeholder="this.$root.$data.NgRemoteMediaTranslations.editor_insert_variations_original_image"
      />

      <input type="hidden" :name="base+'_content_type_identifier_'+fieldId" :value="contentTypeIdentifier"/>
      <input type="hidden" :name="base+'_selected_variation_'+fieldId"/>
     </div>
    <i v-else class="ng-icon ng-spinner" />
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
  props: ["loading", "base", "fieldId", "contentObjectId", "contentVersion", "base", "contentTypeIdentifier", "config", "translations", "selectedImage", "editorInsertAjaxUrl"],
  components: {
    'modal': Modal,
    'interactions': Interactions,
    "v-select": vSelect
  },
  data() {
    return {
      editorInsertModalLoading: false,
    };
  },
  methods: {
    handleVariationChange(value) {
      $('input[name="'+this.base+'_selected_variation_'+this.fieldId+'"]').val(value);
    },
    handleEditorInsertModalSave(){
      this.editorInsertModalLoading = true;

      var data = new FormData();
      data.append('resource_id', $('body').find('input[name="'+this.base+'_media_id_'+this.fieldId+'"]').val());
      data.append('alt_text', $('body').find('input[name="'+this.base+'_alttext_'+this.fieldId+'"]').val());
      data.append('new_file', $('body').find('input[name="'+this.base+'_new_file_'+this.fieldId+'"]')[0].files[0]);
      data.append('image_variations', $('body').find('input[name="'+this.base+'_image_variations_'+this.fieldId+'"]').val());
      data.append('content_type_identifier', $('body').find('input[name="'+this.base+'_content_type_identifier_'+this.fieldId+'"]').val());
      data.append('variation', $('body').find('input[name="'+this.base+'_selected_variation_'+this.fieldId+'"]').val());

      var tagsArray = $('body').find('select[name="'+this.base+'_tags_'+this.fieldId+'[]"]').val();

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
