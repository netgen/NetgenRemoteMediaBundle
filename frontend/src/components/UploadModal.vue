<template>
  <modal title="Upload media" @close="$emit('close')">
    <div :class="loading ? 'loading' : ''">
      <select-folder :selected-folder="selectedFolder" @change="handleFolderChange"></select-folder>

      <div class="input-file-name-wrapper">
        <div v-if="this.error" class="error">
          {{ this.error }}
          <a v-if="this.existingResourceButton" href="#" @click="$emit('uploaded', existingResource)">
            {{ this.$root.$data.NgRemoteMediaTranslations.upload_button_use_existing_resource }}
          </a>
        </div>
        <input type="text" :class="error ? 'error' : ''" v-model="filename"/>

        <v-select
          v-if="visibilities.length > 1"
          :options="visibilities"
          label="name"
          v-model="visibility"
          :reduce="option => option.id"
        />

        <input type="checkbox" v-model="overwrite" id="ngrm-upload-overwrite">
        <label for="ngrm-upload-overwrite">{{ this.$root.$data.NgRemoteMediaTranslations.upload_checkbox_overwrite }}</label>
        <button type="button" class="btn btn-blue" :disabled="filename === '' || visibility === ''" @click="upload">
          {{ this.$root.$data.NgRemoteMediaTranslations.upload_button_save }}
        </button>
      </div>
    </div>
    <i v-if="loading" class="ng-icon ng-spinner" />
  </modal>
</template>

<script>
import SelectFolder from "./SelectFolder";
import Modal from "./Modal";
import vSelect from "vue-select";
import axios from "axios";

export default {
  name: "UploadModal",
  props: ["file", "visibilities"],
  data() {
    return {
      loading: false,
      selectedFolder: "",
      filename: this.file.name,
      visibility: this.visibilities.length > 0 ? this.visibilities[0].id : '',
      overwrite: false,
      error: "",
      existingResourceButton: false,
      existingResource: null,
    };
  },
  components: {
    "select-folder": SelectFolder,
    'modal': Modal,
    "v-select": vSelect
  },
  methods: {
    handleFolderChange(folder) {
      this.selectedFolder = folder;
    },
    async upload() {
      this.loading = true;

      var data = new FormData();

      data.append('file', this.file);
      data.append('filename', this.filename);
      data.append('folder', this.selectedFolder);
      data.append('overwrite', this.overwrite);
      data.append('visibility', this.visibility);

      for (let key in this.$root.$data.NgRemoteMediaOptions.uploadContext) {
        data.append(`upload_context[${key}]`, this.$root.$data.NgRemoteMediaOptions.uploadContext[key]);
      }

      await axios.post(this.$root.$data.config.paths.upload_resources, data)
        .then(response => {
          if (this.$root.$data.NgRemoteMediaOptions.allowedTypes.length > 0 && this.$root.$data.NgRemoteMediaOptions.allowedTypes.indexOf(response.data.type) === -1) {
            this.error = this.$root.$data.NgRemoteMediaTranslations.upload_error_unsupported_resource_type + this.$root.$data.NgRemoteMediaOptions.allowedTypes.join(', ');
            this.loading = false;
          } else {
            this.$emit("uploaded", response.data);
          }
        }).catch(error => {
          if (error.response.status === 409) {
            this.error = this.$root.$data.NgRemoteMediaTranslations.upload_error_existing_resource;
            this.existingResourceButton = true;
            this.existingResource = error.response.data;
            this.loading = false;
          } else {
            this.error = 'Error ' + error.response.status + ' - ' + error.response.statusText;
            this.loading = false;
          }
        });
    }
  },
  watch: {
    visibilities: function() {
      this.visibility = this.visibilities.length > 0 ? this.visibilities[0].id : '';
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

.loading {
  opacity: 0.5;
}

.input-file-name-wrapper {
  padding: 8px 15px;
  background-color: $white;
  box-shadow: inset 1px 0 0 0 $mercury, 0 -1px 0 0 $mercury;
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;

  input[type=text] {
    width: 40%;
    min-width: 300px;
    border: 1px solid $mercury;
    padding: 10px 10px;
    flex-grow: 1;
    margin-right: 10px;

    &.error {
      border: 1px solid red;
    }
  }

  .v-select {
    width: 15%;
    min-width: 150px;
  }

  button {
    float: right;
  }

  div.error {
    color: red;
    margin-bottom: 5px;
  }
}

.ng-spinner {
  position: fixed;
  vertical-align: middle;
  margin-top: 15%;
  left: 50%;
  transform: translate(-50%, -50%);

  &:before {
    display: inline-block;
    animation: spinning 1500ms linear infinite;
  }
}
</style>
