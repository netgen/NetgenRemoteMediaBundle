<template>
  <div class="crop">
    <div class="cropper" :style="cropperStyle" ref="cropper">
      <img :src="src" ref="image" />
      <div class="buttons" ref="buttons" :style="applyButtonStyle">
        <button type="button" class="btn btn-blue" @click="handleReset">
          <span class="ngrm-icon-ccw"></span>
          <span>{{ this.translations.crop_reset }}</span>
        </button>
        <button type="button" class="btn btn-blue" @click="handleApply">
          <span class="ngrm-icon-ok"></span>
          <span>{{ this.translations.crop_apply }}</span>
        </button>
      </div>
    </div>
    <div>
      <h4>{{ this.translations.crop_preview }}</h4>
      <div class="preview" ref="preview"></div>
    </div>
  </div>
</template>

<script>
import Cropper from "cropperjs/src";

export default {
  name: "Crop",
  props: ["translations", "value", "variation", "src", "imageSize"],
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

      const scale = this.$refs.cropper.clientWidth / this.imageSize.width;

      this.cropper = new Cropper(this.$refs.image, {
        viewMode: 2,
        dragMode: "none",
        autoCrop: true,
        data,
        aspectRatio,
        guides: true,
        movable: false,
        rotatable: false,
        guides: false,
        center: false,
        zoomable: false,
        scalable: true,
        minCropBoxWidth: 50,
        minCropBoxHeight: 50,
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
      const scale = this.$refs.cropper ? this.$refs.cropper.clientWidth / this.imageSize.width : 1;
      return {
        top: `${(y + height) * scale + 10}px`,
        left: `${(x + width) * scale - offset -1}px`
      };
    },
    cropperStyle() {
      let ratio = this.imageSize.height / this.imageSize.width * 100;
      return {
        "padding-bottom": `${ratio}%`,
        height: "0px",
        width: `100%`
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
    margin: 0 auto;

    button {
      margin-left: 8px;
    }

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
