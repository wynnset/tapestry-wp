<template>
  <div
    v-if="tapestryIsLoaded"
    id="lightbox"
    :class="{ 'full-screen': node.fullscreen }"
    :format="node.mediaFormat"
  >
    <div v-if="canSkip" class="overlay" @click="close"></div>
    <transition name="lightbox">
      <div
        v-if="isLoaded"
        id="spotlight-content"
        :class="[
          'content',
          {
            'content-text':
              node.mediaType === 'text' || node.mediaType === 'wp-post',
          },
        ]"
        :style="lightboxContentStyles"
      >
        <button v-if="canSkip" class="close-btn" @click="close">
          <div>
            <i class="fa fa-times"></i>
          </div>
        </button>
        <tapestry-media
          :node-id="nodeId"
          :dimensions="dimensions"
          @load="handleLoad"
          @close="close"
        />
      </div>
    </transition>
  </div>
</template>

<script>
import TapestryMedia from "./TapestryMedia"
import Helpers from "../utils/Helpers"
import { mapGetters, mapState } from "vuex"

export default {
  name: "lightbox",
  components: {
    TapestryMedia,
  },
  props: {
    nodeId: {
      type: [String, Number],
      required: true,
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
    lightboxContentStyles() {
      return {
        top: this.dimensions.top + "px",
        left: this.dimensions.left + "px",
        width: this.dimensions.width + "px",
        height: this.dimensions.height + "px",
      }
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
    document.querySelector('body').style.overflow = "hidden"
  },
  beforeDestroy() {
    thisTapestryTool.exitViewMode()
    document.querySelector('body').style.overflow = "auto"
  },
  methods: {
    close() {
      this.$router.push("/")
    },
    handleLoad({ width, height }) {
      if (width && height) {
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

<style lang="scss" scoped>
#lightbox {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  opacity: 1;
  transform: translateY(0);
  z-index: 100;

  .overlay {
    width: 100%;
    height: 100%;
    position: fixed;
  }

  .content {
    position: absolute;
    top: 5vh;
    left: 5vw;
    height: 90vh;
    width: 90vw;
    z-index: 100;
    background-position: 0 0;
    background-size: cover;
    background-color: black;
    box-shadow: 0 0 100px -40px #000;
    border-radius: 15px;
    scrollbar-color: rgba(0,0,0,.5) rgba(255,255,255,.5) ;
    scrollbar-width: auto;
    -ms-overflow-style:scrollbar;

    ::-webkit-scrollbar {
      -webkit-appearance: none;
      width: 12px;
    }

    ::-webkit-scrollbar-thumb {
      border-radius: 4px;
      background-color: rgba(0,0,0,.5);
      box-shadow: 0 0 1px rgba(255,255,255,.5);
    }

    ::-webkit-scrollbar-corner {
      display: none;
    }

    &.content-text {
      outline: none;
      background: #eee;
      color: #333;
      padding: 1em;

      .media-wrapper {
        height: 100%;
        overflow: scroll;
        background: transparent;
      }
    }
  }

  .close-btn {
    width: auto;
    display: flex;
    justify-content: flex-end;
    background: none;
    border: none;
    outline: none;
    position: absolute;
    top: -37px;
    right: -42px;
    z-index: 20;

    > div {
      background: #666;
      color: white;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      font-size: 1.2em;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.16);
      transition: all 0.25s ease;
      border: solid 2px #fff;

      &:hover {
        transform: scale(1.15);
        background: #11a6d8;
      }

      i {
        background: none;
      }
    }
  }

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
      top: 0 !important;
      right: 0 !important;
      z-index: 100;
    }
  }
}
</style>

<style lang="scss">
.lightbox-enter-active,
.lightbox-leave-active {
  transition: all 1s;
}

.lightbox-enter,
.lightbox-leave-to {
  opacity: 0;
  transform: translateY(32px);
}
</style>
