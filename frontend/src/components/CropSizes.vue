<template>
  <div class="sidebar-crop">
    <div class="buttons">
      <button v-if="!addingVariations" type="button" class="btn" @click="handleAddCropSize">Add crop size</button>
      <button v-if="addingVariations" type="button" class="btn" @click="handleCancel">Cancel</button>
      <button v-if="addingVariations" type="button" class="btn crop-btn-add" @click="handleAdd">Add</button>
    </div>
    <div class="sidebar-crop-label">
        <span>Addded for Confirmation</span>
    </div>
    <div v-show="addingVariations" :class="{ unselectedVariations: addingVariations }">
      <div v-for="name in unselectedVariations" :key="name" :class="{ disabled: !isVariationSelectable(name) }">
        <input type="checkbox" :id="name" :value="name" v-model="newSelection" :disabled="!isVariationSelectable(name)" />
        <label :for="name">
          <span class="name">{{name}}</span>
          <span class="formatted-size">{{formattedSize(name)}}</span>
        </label>
        <div v-if="!isVariationSelectable(name)" class="legend-not-selectable">
          <span>Media is too small.</span>
        </div>
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
          <div>
            <span class="name">{{name}}</span>
            <span class="formatted-size">{{formattedSize(name)}}</span>
          </div>
          <a v-if="!addingVariations" href="#">
            <span class="circle-orange"></span>
            <span class="icon-trash" @click="removeItem(name)"></span>
          </a>
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
      const available = Object.keys(this.availableVariations);
      const set = Object.keys(this.allVariationValues);

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
@import "../scss/variables";

.sidebar-crop {
  width: 264px;
  flex-shrink: 0;
  box-shadow: inset -1px 0 0 0 $mercury;

  .buttons {
    background: $white;
    padding: 15px;
    display: flex;
    box-shadow: inset 0 -1px 0 0 $mercury;
    margin-right: 1px;

    button {
      flex-grow: 1;

      &.crop-btn-add {
        margin-left: 10px;
      }
    }

    button:only-child {
      width: 100%;
    }
  }
}

.sidebar-crop-label span {
	color: $dusty-gray;
	font-size: 14px;
    line-height: 18px;
    display: inline-block;
    padding: 31px 15px 15px;
    width: 100%;
    box-shadow: inset 0 -1px 0 0 $mercury;
}

.unselectedVariations {
  position: absolute;
  top: 0;
  left: 0;
  width: 264px;
  height: 100%;
  transform: translateX(264px);
  background: $white;
  box-shadow: inset -1px 0 0 0 $mercury;
  z-index: 10;

  > div {
    padding: 15px;
    display: flex;
    align-items: flex-start;
    background-color: $white;
    box-shadow: inset 0 -1px 0 0 $mercury, inset -1px 0 0 0 $mercury;

    input, label {
      cursor: pointer;
    }

    input {
      margin-right: 11px;
    }

    label {
      width: 100%;
    }

    .name {
      color: $mine-shaft;
      font-size: 14px;
      line-height: 18px;
    }

    .formatted-size {
      color: $dusty-gray;
      font-size: 12px;
      line-height: 18px;
      display: block;
    }
  }

  > div.disabled {
    flex-wrap: wrap;
    background-color: $wild-sand;
    cursor: auto;
    color: #ddd;
    padding: 15px 15px 5px;

    label, input, span {
      cursor: auto;
    }

    label {
      flex-grow: 1;
      width: auto;
      color: $dusty-gray;
    }
  }

  .legend-not-selectable {
    width: 100%;
    font-size: .75rem;
    color: #a41034;
    display: inline-block;
    text-align: right;
  }
}

.selectedVariations {
  ul {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      padding: 15px 0 15px 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: inset -1px 0 0 0 $mercury, inset 0 -1px 0 0 $mercury;
      background-color: $white;
      cursor: pointer;

      &.disabled {
        background-color: $wild-sand;
        cursor: auto;
      }

      &.selected {
        &.set {
          color: lightgreen;
        }
      }

      span {
        display: block;
        color: $mine-shaft;
        font-size: 14px;
        line-height: 18px;
      }

      a {
        display: flex;
        align-items: center;

        span {
          padding: 5px;
        }

        .icon-trash {
          color: $netgen-primary;
          padding: 10px;
        }
      }

      .formatted-size {
        color: $dusty-gray;
        font-size: 12px;
        line-height: 18px;
      }

      .circle-orange {
        width: 8px;
        height: 8px;
        background-color: orange;
        border-radius: 50%;
      }
    }
  }
  .set {
    .circle-orange {
      display: none;
    }
  }
}
</style>
