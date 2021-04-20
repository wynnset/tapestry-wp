<template>
  <tapestry-modal
    id="lightbox"
    data-qa="lightbox"
    :class="{
      'full-screen': node.fullscreen,
      'content-text': node.mediaType === 'text' || node.mediaType === 'wp-post',
    }"
    :node-id="nodeId"
    :content-container-style="lightboxContentStyles"
    :allow-close="canSkip"
    @close="handleUserClose"
  >
    <multi-content-media
      v-if="node.mediaType === 'multi-content'"
      :node="node"
      :row-id="rowId"
      :sub-row-id="subRowId"
      @close="handleAutoClose"
      @complete="complete"
    />
    <page-navigation-bar
      v-if="node.typeData.showNavBar && node.presentationStyle === 'page'"
      :node="node"
      :rowRefs="rowRefs"
      :dimensions="dimensions"
    />
    <tapestry-media
      v-if="node.mediaType !== 'multi-content'"
      :node-id="nodeId"
      :dimensions="dimensions"
      context="lightbox"
      @load="handleLoad"
      @close="handleAutoClose"
      @complete="complete"
      @change:dimensions="updateDimensions"
    />
  </tapestry-modal>
</template>

<script>
import { mapActions, mapGetters, mapState } from "vuex"
import client from "@/services/TapestryAPI"
import TapestryModal from "./TapestryModal"
import MultiContentMedia from "./media/MultiContentMedia"
import TapestryMedia from "./media/TapestryMedia"
import PageNavigationBar from "./media/MultiContentMedia/PageNavigationBar"
import { names } from "@/config/routes"
import Helpers from "@/utils/Helpers"
import { sizes } from "@/utils/constants"
import DragSelectModular from "@/utils/dragSelectModular"

export default {
  name: "lightbox",
  components: {
    MultiContentMedia,
    TapestryMedia,
    TapestryModal,
    PageNavigationBar,
  },
  props: {
    nodeId: {
      type: Number,
      required: true,
    },
    rowId: {
      type: Number,
      required: false,
      default: 0,
    },
    subRowId: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  data() {
    return {
      dimensions: {
        top: 100,
        left: 50,
      },
      showCompletionScreen: false,
      rowRefs: [],
    }
  },
  computed: {
    ...mapState(["h5pSettings", "rootId"]),
    ...mapGetters(["getNode", "isMultiContent", "isMultiContentRow"]),
    node() {
      const node = this.getNode(this.nodeId)
      return node
    },
    canSkip() {
      return this.node.completed || this.node.skippable !== false
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
        styles.width = "100vw"
        styles.height = "100vh"
        styles.position = "relative"

        const adminBar = document.getElementById("wpadminbar")
        if (adminBar) {
          styles.top = `${adminBar.clientHeight}px`
          styles.height = `calc(100vh - ${styles.top})`
        }
      }

      if (this.node.mediaType === "multi-content") {
        styles.display = "flex"
        // Reversed because PageNavigationBar is placed after MultiContentMedia for refs to correctly render
        styles.flexDirection = "row-reverse"
        return Object.assign(styles, { padding: "24px" })
      }

      if (this.node.mediaType === "text" || this.node.mediaType === "wp-post") {
        return Object.assign(styles, {
          background: "#eee",
          color: "#333",
          padding: "1em",
        })
      }

      return styles
    },
    lightboxDimensions() {
      if (!this.node) {
        return {}
      }

      const { mediaWidth: width, mediaHeight: height } = this.node.typeData
      const browserWidth = Helpers.getBrowserWidth()
      const browserHeight = Helpers.getBrowserHeight()

      if (this.node.fullscreen) {
        return {
          width: browserWidth,
          height: browserHeight,
        }
      }

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

      const nodeSpace = sizes.NODE_RADIUS * 2 * 1.3
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
    nodeId: {
      immediate: true,
      handler() {
        if (!this.node) {
          // Node ID doesn't exist, so reroute to default selected node
          this.$router.replace({
            name: names.APP,
            params: { nodeId: this.rootId },
            query: this.$route.query,
          })
        } else {
          this.applyDimensions()
        }
      },
    },
    rowId: {
      immediate: true,
      handler(rowId) {
        if (rowId) {
          if (
            !this.isMultiContent(this.nodeId) ||
            !this.isMultiContentRow(rowId, this.nodeId)
          ) {
            this.$router.replace({
              name: names.LIGHTBOX,
              params: { nodeId: this.nodeId },
              query: this.$route.query,
            })
          }
        }
      },
    },
    subRowId: {
      immediate: true,
      handler(subRowId) {
        if (subRowId) {
          if (!this.isMultiContentRow(subRowId, this.rowId)) {
            this.$router.replace({
              name: names.ACCORDION,
              params: { nodeId: this.nodeId, rowId: this.rowId },
              query: this.$route.query,
            })
          }
        }
      },
    },
  },
  mounted() {
    document.querySelector("body").classList.add("tapestry-lightbox-open")
    DragSelectModular.removeDragSelectListener()
    if (this.node.mediaType === "multi-content") {
      this.$root.$on("observe-rows", refs => {
        this.rowRefs = this.rowRefs.concat(refs)
      })
    }
  },
  beforeDestroy() {
    document.querySelector("body").classList.remove("tapestry-lightbox-open")
    DragSelectModular.addDragSelectListener()
    this.$router.push({
      ...this.$route,
      query: { ...this.$route.query, row: undefined },
    })
  },
  methods: {
    ...mapActions(["completeNode"]),
    complete() {
      this.completeNode(this.nodeId)
    },
    handleUserClose() {
      client.recordAnalyticsEvent("user", "close", "lightbox", this.nodeId)
      this.close()
    },
    handleAutoClose() {
      client.recordAnalyticsEvent("app", "close", "lightbox", this.nodeId)
      this.close()
    },
    close() {
      this.$router.push({
        name: names.APP,
        params: { nodeId: this.nodeId },
        query: this.$route.query,
      })
    },
    handleLoad(dimensions) {
      if (dimensions) {
        const { width, height } = dimensions
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
}
</style>

<style lang="scss" scoped>
.content-text {
  .media-wrapper {
    overflow: auto;
  }
}

#lightbox {
  &.full-screen {
    background: #000;

    .close-btn {
      position: fixed;
      top: 50px;
      right: 50px;
    }
  }
  height: 100%;
}
</style>
