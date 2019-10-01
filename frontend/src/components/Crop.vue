<template>
  <div class="crop">
    <div class="cropper" :style="cropperStyle">
      <img :src="src" ref="image" />
      <div class="buttons" ref="buttons" :style="applyButtonStyle">
        <button type="button" @click="handleReset">Reset</button>
        <button type="button" @click="handleApply">Apply</button>
      </div>
    </div>
    <h4>Preview</h4>
    <div class="preview" ref="preview"></div>
  </div>
</template>

<script>
import Cropper from "cropperjs/src";

export default {
  name: "Crop",
  props: ["value", "variation", "src", "imageSize"],
  mounted() {
    this.setCropper();
  },
  beforeDestroy: function() {
    this.destroyCropper();
  },
  data() {
    return {
      crop: {},
      cropper: null
    };
  },
  methods: {
    setCropper() {
      const { x, y, w, h } = this.value || {},
        data = { x, y, width: w, height: h },
        [variationWidth, variationHeight] = this.variation,
        aspectRatio =
          variationWidth > 0 && variationHeight > 0
            ? variationWidth / variationHeight
            : undefined;

      this.destroyCropper();

      this.cropper = new Cropper(this.$refs.image, {
        viewMode: 2,
        dragMode: "none",
        autoCrop: true,
        data,
        aspectRatio,
        guides: true,
        movable: false,
        rotatable: false,
        zoomable: false,
        scalable: true,
        minCropBoxWidth: variationWidth,
        minCropBoxHeight: variationHeight,
        crop: this.handleCrop,
        preview: this.$refs.preview
      });

      this.cropper.setData(data);
    },
    handleCrop(event) {
      this.crop = this.cropper.getData(true);
    },
    destroyCropper() {
      this.cropper && this.cropper.destroy();
    },
    handleReset() {
      this.cropper.reset();
    },
    handleApply() {
      const { x, y, width, height } = this.cropper.getData(true);
      this.$emit("change", { x, y, w: width, h: height });
    }
  },

  computed: {
    applyButtonStyle() {
      const { x, y, width, height } = this.crop;
      const offset = this.$refs.buttons ? this.$refs.buttons.clientWidth : 0;
      return {
        top: `${y + height + 10}px`,
        left: `${x + width - offset}px`
      };
    },
    cropperStyle() {
      return {
        height: `${this.imageSize.height}px`,
        width: `${this.imageSize.width}px`
      };
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
.crop {
  .cropper {
    position: relative;

    .buttons {
      position: absolute;
    }
  }
  .preview {
    width: 100%;
    height: 500px;
    overflow: hidden;
  }
}
</style>
