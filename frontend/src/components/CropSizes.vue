<template>
  <div>
    <div class="buttons">
      <button v-if="!addingVariations" type="button" @click="handleAddCropSize">Add crop size</button>
      <button v-if="addingVariations" type="button" @click="handleCancel">Cancel</button>
      <button v-if="addingVariations" type="button" @click="handleAdd">Add</button>
    </div>
    <div v-if="addingVariations" class="unselectedVariations">
      <div v-for="name in unselectedVariations" :key="name">
        <input type="checkbox" :id="name" :value="name" v-model="newSelection" />
        <label :for="name">
          <span>{{name}}</span>
          <span>{{formattedSize(name)}}</span>
        </label>
      </div>
    </div>
    <div class="selectedVariations">
      <ul>
        <li
          v-for="name in selectedVariations"
          :key="name"
          :class="{set: !!allVariationValues[name], selected: selectedVariation === name, disabled: !isVariationSelectable(name)}"
          @click="handleVariationClicked(name)"
        >
          <span>{{name}}</span>
          <span>{{formattedSize(name)}}</span>
          <a v-if="!addingVariations" href="#" @click.prevent.stop="removeItem(name)">remove</a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: "CropSizes",
  props: [
    "availableVariations",
    "allVariationValues",
    "imageSize",
    "selectedVariation"
  ],
  data() {
    return {
      newSelection: [],
      addingVariations: false
    };
  },
  computed: {
    unselectedVariations() {
      const available = Object.getOwnPropertyNames(this.availableVariations);
      const set = Object.getOwnPropertyNames(this.allVariationValues);

      return available.difference(set);
    },
    selectedVariations() {
      return Object.getOwnPropertyNames(this.allVariationValues);
    }
  },
  methods: {
    handleAddCropSize() {
      this.addingVariations = true;
    },
    handleCancel() {
      this.addingVariations = false;
      this.newSelection = [];
    },
    handleAdd() {
      this.$emit("addedVariations", this.newSelection);
      this.newSelection = [];
      this.addingVariations = false;
    },
    removeItem(name) {
      this.$emit("removedVariation", name);
    },
    formattedSize(name) {
      return `${this.availableVariations[name][0]} x ${
        this.availableVariations[name][1]
      }`;
    },
    isVariationSelectable(name) {
      if (this.addingVariations) {
        return false;
      }
      const [width, height] = this.availableVariations[name];
      return this.imageSize.width >= width && this.imageSize.height >= height;
    },
    handleVariationClicked(name) {
      if (this.isVariationSelectable(name)) {
        this.$emit("selected", name);
      }
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
.selectedVariations {
  .set {
    color: green;
  }

  .disabled {
    color: red !important;
  }

  .selected {
    background-color: gray;
    color: white;
    &.set {
      color: lightgreen;
    }
  }
}
</style>
