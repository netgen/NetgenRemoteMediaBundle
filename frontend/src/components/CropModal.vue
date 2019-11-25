<template>
  <modal title="Crop" @close="$emit('close')">
    <crop-sizes
      :availableVariations="availableVariations"
      :allVariationValues="allVariationValues"
      :imageSize="imageSize"
      :selectedVariation="selectedVariation"
      @selected="handleVariationSelected"
      @addedVariations="handleAddedVariations"
      @removedVariation="handleRemovedVariation"
    />
    <div v-for="(variationSize, variation) in availableVariations" :key="variation" class="crop-container">
      <crop
        v-if="variation===selectedVariation"
        :value="allVariationValues[variation]"
        :src="selectedImage.url"
        :variation="availableVariations[variation]"
        :imageSize="imageSize"
        @change="val => handleVariationValueChange(variation, val)"
      ></crop>
    </div>
    <div v-if="!selectedVariation" class="img-placeholder">
      <img :src="selectedImage.url" />
    </div>
    <div class="action-strip">
      <button type="button" class="btn" @click="handleCancelClicked">Cancel</button>
      <button type="button" class="btn btn-blue" @click="handleSaveClicked">
        <span class="icon-floppy"></span>
        <span>Save sizes</span>
      </button>
    </div>
  </modal>
</template>

<script>
import Modal from "./Modal";
import CropSizes from "./CropSizes";
import Crop from "./Crop";

import { objectFilter } from "../utility/functional";
import { notUndefined } from "../utility/predicates";

export default {
  name: "CropModal",
  props: ["availableVariations", "selectedImage"],
  components: {
    modal: Modal,
    "crop-sizes": CropSizes,
    crop: Crop
  },
  data() {
    return {
      selectedVariation: null,
      newVariationValues: {}
    };
  },
  computed: {
    allVariationValues() {
      return objectFilter(notUndefined)({
        ...this.selectedImage.variations,
        ...this.newVariationValues
      });
    },
    imageSize() {
      return {
        height: this.selectedImage.height,
        width: this.selectedImage.width
      };
    }
  },
  methods: {
    handleVariationSelected(name) {
      this.selectedVariation = name;
    },
    handleAddedVariations(names) {
      this.newVariationValues = {
        ...this.newVariationValues,
        ...names.reduce((obj, name) => {
          obj[name] = null;
          return obj;
        }, {})
      };
    },
    handleRemovedVariation(name) {
      this.newVariationValues = {
        ...this.newVariationValues,
        [name]: undefined
      };
    },
    handleVariationValueChange(variation, value) {
      this.newVariationValues = {
        ...this.newVariationValues,
        [variation]: value
      };
    },
    handleCancelClicked() {
      this.newVariationValues = {};
      this.$emit("close");
    },
    handleSaveClicked() {
      this.$emit("change", { ...this.newVariationValues });
      this.newVariationValues = {};
      this.$emit("close");
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import '../scss/variables';

.crop-container {
  overflow-y: auto;
  flex-grow: 1;
  margin: 30px 30px 80px;

  &:empty {
    display: none;
  }
}

.img-placeholder {
  flex-grow: 1;
  padding: 60px 60px 110px;

  img {
    max-width: 100%;
    height: auto;
    margin: 0 auto;
    display: block;
    max-width: 100%;
    height: 100%;
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
  left: 264px;
  right: 0;

  button {
    margin-left: 10px;
  }

  .icon-floppy {
    margin-right: 5px;
  }
}
</style>
