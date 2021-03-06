<template>
  <div
    :class="[
      'media-wrapper',
      { 'media-wrapper-embed': node.mediaFormat === 'embed' },
      {
        'media-wrapper-no-scroll':
          node.mediaFormat === 'mp4' ||
          node.mediaFormat === 'h5p' ||
          node.mediaFormat === 'youtube',
      },
    ]"
    @mouseenter="setHovered"
  >
    <text-media
      v-if="node.mediaType === 'text'"
      :node="node"
      :context="context"
      @complete="complete"
      @load="handleLoad"
    />
    <video-media
      v-if="isVideoNode"
      :dimensions="dimensions"
      :context="context"
      :node-id="nodeId"
      @change:dimensions="$emit('change:dimensions', $event)"
      @load="handleLoad"
      @update-progress="updateProgress"
      @complete="complete"
      @close="$emit('close')"
    />
    <h5p-media
      v-if="node.mediaType === 'h5p' && !isVideoNode"
      :dimensions="dimensions"
      :context="context"
      :node="node"
      @change:dimensions="$emit('change:dimensions', $event)"
      @load="handleLoad"
      @complete="complete"
      @close="$emit('close')"
    />
    <external-media
      v-if="node.mediaType === 'url-embed'"
      :dimensions="dimensions"
      :node="node"
      :context="context"
      @load="handleLoad"
      @complete="complete"
    />
    <wp-post-media
      v-if="node.mediaType === 'wp-post'"
      :node="node"
      :context="context"
      @complete="complete"
      @load="handleLoad"
    ></wp-post-media>
    <activity-media
      v-if="node.mediaType === 'activity'"
      :dimensions="dimensions"
      :node="node"
      :context="context"
      @change:dimensions="$emit('change:dimensions', $event)"
      @complete="complete"
      @close="$emit('close')"
      @load="handleLoad"
    />
    <answer-media
      v-if="node.mediaType === 'answer'"
      :node="node"
      @complete="complete"
      @close="$emit('close')"
      @load="handleLoad"
    />
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex"
import TextMedia from "./TextMedia"
import VideoMedia from "./VideoMedia"
import H5PMedia from "./H5PMedia"
import ExternalMedia from "./ExternalMedia"
import ActivityMedia from "./ActivityMedia"
import AnswerMedia from "./AnswerMedia"
import WpPostMedia from "./WpPostMedia"

export default {
  name: "tapestry-media",
  components: {
    TextMedia,
    VideoMedia,
    "h5p-media": H5PMedia,
    ExternalMedia,
    WpPostMedia,
    ActivityMedia,
    AnswerMedia,
  },
  props: {
    nodeId: {
      type: [Number, String],
      required: true,
    },
    dimensions: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    context: {
      type: String,
      required: false,
      default: "lightbox",
    },
  },
  data() {
    return {
      timeSinceLastSaved: new Date(),
    }
  },
  computed: {
    ...mapGetters(["getNode"]),
    node() {
      return this.getNode(this.nodeId)
    },
    isVideoNode() {
      if (this.node.mediaType === "h5p" || this.node.mediaFormat === "h5p") {
        if (this.node.typeData.h5pMeta.library === "H5P.InteractiveVideo") {
          return true
        }
      }
      return ["mp4", "youtube"].includes(this.node.mediaFormat)
    },
  },
  beforeDestroy() {
    this.updateNodeProgress({
      id: this.nodeId,
      progress: this.node && this.node.progress,
    })
  },
  methods: {
    ...mapActions(["updateNodeProgress"]),
    setHovered() {
      this.$router.push({
        ...this.$route,
        query: {
          ...this.$route.query,
          row: this.nodeId,
        },
      })
    },
    handleLoad(args) {
      this.$emit("load", args)
    },
    updateProgress({ amountViewed }) {
      this.updateNodeProgress({ id: this.nodeId, progress: amountViewed })
    },
    complete(nodeId) {
      this.$emit("complete", nodeId)
    },
  },
}
</script>

<style lang="scss" scoped>
.media-wrapper {
  outline: none;
  border-radius: 15px;
  overflow: auto;
  height: 100%;
  padding: 0;

  &-no-scroll {
    overflow: hidden;
  }

  &-embed {
    background: white;
  }
}
</style>
