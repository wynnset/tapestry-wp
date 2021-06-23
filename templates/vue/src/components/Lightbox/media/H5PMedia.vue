<template>
  <div
    ref="h5pMediaIFrameContainer"
    class="h5p-media-iframe-container"
    :class="{
      'context-multi-content': hasMultiContentContext,
    }"
    :style="{
      height: `${dimensions.height}px`,
      width: '100%',
    }"
  >
    <iframe
      id="h5p"
      ref="h5p"
      height="100%"
      width="100%"
      frameborder="0"
      :src="node.typeData && node.typeData.mediaURL"
      :scrolling="scrollingValue"
      @load="handleLoad"
      @close="$emit('close')"
    ></iframe>
  </div>
</template>

<script>
export default {
  name: "h5p-media",
  props: {
    node: {
      type: Object,
      required: true,
    },
    dimensions: {
      type: Object,
      required: true,
    },
    context: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      library: null,
      frameHeight: null,
      frameWidth: null,
      refreshed: false,
    }
  },
  computed: {
    scrollingValue() {
      const noscroll = ["H5P.ThreeImage"]
      if (noscroll.includes(this.library)) {
        return "no"
      } else return "auto"
    },
    hasMultiContentContext() {
      return this.context === "multi-content" || this.context === "page"
    },
  },
  methods: {
    handleLoad() {
      const h5pObj = this.$refs.h5p.contentWindow.H5P
      const h5pInstance = h5pObj.instances[0]
      const loadedH5PId = h5pInstance.contentId

      this.library = h5pInstance.libraryInfo.machineName
      this.frameHeight = this.dimensions.height
      this.frameWidth = this.dimensions.width

      // Check to see whether this is an H5P recorder
      // If it is, we can emit an event to load the recorded audio (if exists)
      // and terminate
      if (h5pInstance.recorder && loadedH5PId) {
        this.loadedH5PRecorderId = loadedH5PId
        this.h5pRecorderSaverIsLoaded()
        return
      }

      this.frameHeight = this.$refs.h5p.contentWindow.document.activeElement.children[0].clientHeight
      this.$emit("change:dimensions", { height: this.frameHeight })

      switch (this.library) {
        case "H5P.ThreeImage":
          {
            let threeSixtyLoadInterval = setInterval(() => {
              if (typeof h5pInstance.reDraw !== "undefined") {
                clearInterval(threeSixtyLoadInterval)
                if (this.node.typeData && this.node.typeData.scene) {
                  h5pInstance.currentScene = this.node.typeData.scene
                  h5pInstance.reDraw()
                }
              }
              this.$emit("load")
              this.$emit("complete")
            }, 500)
          }
          break
        default:
          this.$emit("load")
          this.$emit("complete")
          break
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.container {
  position: relative;
  width: 100%;
  height: 100%;
  max-width: 100vw;
  padding: 0;
}

.h5p-media-iframe-container {
  margin: auto;
  overflow: hidden;
  border-radius: 15px;
  &:not(.context-multi-content) {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: auto;
  }
  iframe {
    margin: -1px !important;
    min-width: calc(100% + 4px);
  }
}
</style>
