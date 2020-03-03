<template>
  <tapestry-modal
    v-if="tapestryIsLoaded"
    id="lightbox"
    :class="{
      'full-screen': node.fullscreen,
      'content-text': node.mediaType === 'text' || node.mediaType === 'wp-post',
    }"
    :content-container-style="lightboxContentStyles"
    :close-button-style="closeButtonStyles"
    :allow-close="canSkip"
    @close="close"
  >
    <accordion-media
      v-if="node.mediaType === 'accordion'"
      :node="node"
      @close="close"
    />
    <tapestry-media
      v-else
      :node-id="nodeId"
      :dimensions="dimensions"
      @load="handleLoad"
      @close="close"
    />
  </tapestry-modal>
</template>

<script>
import TapestryModal from "./TapestryModal"
import AccordionMedia from "./lightbox/AccordionMedia"
import TapestryMedia from "./TapestryMedia"
import Helpers from "../utils/Helpers"
import { mapGetters, mapState } from "vuex"

export default {
  name: "lightbox",
  components: {
    AccordionMedia,
    TapestryMedia,
    TapestryModal,
  },
  props: {
    nodeId: {
      type: [String, Number],
      required: false,
      default: 0,
    },
  },
  data() {
    return {
      isLoaded: false,
      dimensions: {
        top: 100,
        left: 50,
      },
      timeSinceLastSaved: new Date(),
      showCompletionScreen: false,
    }
  },
  computed: {
    ...mapState(["h5pSettings", "tapestryIsLoaded"]),
    ...mapGetters(["getNode"]),
    node() {
      return this.getNode(this.nodeId)
    },
    canSkip() {
      return this.node.completed || this.node.skippable !== false
    },
    closeButtonStyles() {
      return this.node.fullscreen
        ? {
            position: "fixed",
            top: "16px",
            right: "16px",
          }
        : {}
    },
    lightboxContentStyles() {
      const styles = {
        top: this.dimensions.top + "px",
        left: this.dimensions.left + "px",
        width: this.dimensions.width + "px",
        height: this.dimensions.height + "px",
      }

      if (this.node.fullscreen) {
        styles.top = "auto"
        styles.left = "auto"
        styles.width = "auto"
        styles.height = "100%"
        styles.position = "relative"
      }

      if (this.node.mediaType === "text" || this.node.mediaType === "wp-post") {
        return Object.assign(styles, {
          background: "#eee",
          color: "#333",
        })
      }

      return styles
    },
    lightboxDimensions() {
      const NORMAL_RADIUS = 140 // TODO: Refactor this to "constants" folder
      if (!this.node) {
        return {}
      }

      const { mediaWidth: width, mediaHeight: height } = this.node.typeData
      const browserWidth = Helpers.getBrowserWidth()
      const browserHeight = Helpers.getBrowserHeight()

      let resizeRatio = 1
      let videoWidth = width
      let videoHeight = height

      if (width > Helpers.getBrowserWidth()) {
        resizeRatio *= browserWidth / width
        videoWidth *= resizeRatio
        videoHeight *= resizeRatio
      }

      if (videoHeight > browserHeight * resizeRatio) {
        resizeRatio *= browserHeight / videoHeight
        videoWidth *= resizeRatio
        videoHeight *= resizeRatio
      }

      const nodeSpace = NORMAL_RADIUS * 2 * 1.3
      const adjustedVideoHeight = Math.min(videoHeight, browserHeight - nodeSpace)
      const adjustedVideoWidth = Math.min(videoWidth, browserWidth - nodeSpace)

      const heightAdjustmentRatio = adjustedVideoHeight / videoHeight
      const widthAdjustmentRatio = adjustedVideoWidth / videoWidth
      let adjustmentRatio = widthAdjustmentRatio
      let adjustedOn = "width"

      if (Helpers.getAspectRatio() < 1) {
        adjustedOn = "height"
        adjustmentRatio = heightAdjustmentRatio
      }

      adjustmentRatio *= 0.95
      return {
        adjustedOn,
        width: videoWidth * adjustmentRatio,
        height: videoHeight * adjustmentRatio,
      }
    },
  },
  watch: {
    tapestryIsLoaded() {
      this.applyDimensions()
    },
    nodeId() {
      this.applyDimensions()
      thisTapestryTool.selectNode(Number(this.nodeId))
    },
  },
  mounted() {
    this.isLoaded = true
    this.applyDimensions()
    thisTapestryTool.selectNode(Number(this.nodeId))
    thisTapestryTool.changeToViewMode(this.lightboxDimensions)
    document.querySelector("body").classList.add("tapestry-lightbox-open")
  },
  beforeDestroy() {
    thisTapestryTool.exitViewMode()
    document.querySelector("body").classList.remove("tapestry-lightbox-open")
  },
  methods: {
    close() {
      this.$router.push("/")
    },
    handleLoad(opts) {
      if (opts && opts.width && opts.height) {
        const { width, height } = opts
        this.updateDimensions({ width, height })
      }
    },
    updateDimensions(dimensions) {
      this.dimensions = {
        ...this.dimensions,
        ...dimensions,
      }
    },
    applyDimensions() {
      this.dimensions = {
        ...this.dimensions,
        left: (Helpers.getBrowserWidth() - this.lightboxDimensions.width) / 2,
        width: this.lightboxDimensions.width,
        height: this.lightboxDimensions.height,
      }
    },
  },
}
</script>

<style lang="scss">
body.tapestry-lightbox-open {
  overflow: hidden;
  overflow: hidden;
}
</style>

<style lang="scss" scoped>
.content-text {
  .media-wrapper {
    overflow-y: scroll;
  }
}

#lightbox {
  &.full-screen {
    background: #000;

    &[format="h5p"] #spotlight-content {
      width: 100vw !important;
      height: 100vh !important;

      iframe {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }
    }
    &[format="mp4"] #spotlight-content video {
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 100% !important;
      max-width: 100vw !important;
      max-height: 100vh !important;
    }

    #spotlight-content {
      top: 0 !important;
      left: 0 !important;
      width: auto !important;
      height: auto !important;
      width: 100vw !important;
      height: 100vh !important;
      border-radius: 0;
    }

    .close-btn {
      position: fixed;
      top: 50px;
      right: 50px;
    }
  }
}
</style>
