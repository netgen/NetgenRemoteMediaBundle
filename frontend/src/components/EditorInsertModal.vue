<template>
  <modal :title="this.$root.$data.NgRemoteMediaTranslations.editor_insert_title" class="editor-insert-modal" @close="$emit('close')">
    <div v-if="!loading" class="editor-insert-modal-body">
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
      <button type="button" class="btn btn-blue" @click="$emit('media-inserted', item)">
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
  props: ["loading", "base", "fieldId", "contentObjectId", "contentVersion", "base", "contentTypeIdentifier", "config", "translations", "selectedImage"],
  components: {
    'modal': Modal,
    'interactions': Interactions,
    "v-select": vSelect
  },
  methods: {
    handleVariationChange(value) {
      $('input[name="'+this.base+'_selected_variation_'+this.fieldId+'"]').val(value);
    },
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
</style>
