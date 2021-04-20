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
  >
    <text-media
      v-if="node.mediaType === 'text'"
      :node="node"
      :context="context"
      :showTitle="showTitle()"
      @complete="complete"
      @load="handleLoad"
    />
    <video-media
      v-if="node.mediaFormat === 'mp4'"
      :autoplay="autoplay"
      :node="node"
      :showTitle="showTitle()"
      :dimensions="dimensions"
      :context="context"
      @load="handleLoad"
      @complete="complete"
      @timeupdate="updateProgress"
      @close="$emit('close')"
    />
    <youtube-media
      v-if="node.mediaFormat === 'youtube'"
      :autoplay="autoplay"
      :node="node"
      :showTitle="showTitle()"
      :dimensions="dimensions"
      :context="context"
      @load="handleLoad"
      @complete="complete"
      @timeupdate="updateProgress"
      @close="$emit('close')"
    />
    <external-media
      v-if="node.mediaType === 'url-embed'"
      :dimensions="dimensions"
      :node="node"
      :showTitle="showTitle()"
      :context="context"
      @load="handleLoad"
      @complete="complete"
    />
    <h5p-media
      v-if="node.mediaFormat === 'h5p'"
      :autoplay="autoplay"
      :dimensions="dimensions"
      :context="context"
      :node="node"
      :showTitle="showTitle()"
      @change:dimensions="$emit('change:dimensions', $event)"
      @load="handleLoad"
      @timeupdate="updateProgress"
      @complete="complete"
      @close="$emit('close')"
    />
    <gravity-form
      v-if="node.mediaType === 'gravity-form' && !showCompletionScreen"
      :id="node.typeData.mediaURL"
      :node="node"
      :showTitle="showTitle()"
      :context="context"
      @submit="handleFormSubmit"
      @load="handleLoad"
    ></gravity-form>
    <wp-post-media
      v-if="node.mediaType === 'wp-post'"
      :node="node"
      :showTitle="showTitle()"
      :context="context"
      @complete="complete"
      @load="handleLoad"
    ></wp-post-media>
    <activity-media
      v-if="node.mediaType === 'activity'"
      :node="node"
      :showTitle="showTitle()"
      :context="context"
      @complete="complete"
      @close="$emit('close')"
      @load="handleLoad"
    />
    <completion-screen v-if="showCompletionScreen" />
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex"
import TextMedia from "./TextMedia"
import VideoMedia from "./VideoMedia"
import ExternalMedia from "./ExternalMedia"
import H5PMedia from "./H5PMedia"
import ActivityMedia from "./ActivityMedia"
import YouTubeMedia from "./YouTubeMedia"
import WpPostMedia from "./WpPostMedia"
import GravityForm from "./common/GravityForm"
import CompletionScreen from "./common/ActivityScreen/CompletionScreen"

export default {
  name: "tapestry-media",
  components: {
    TextMedia,
    VideoMedia,
    ExternalMedia,
    "h5p-media": H5PMedia,
    GravityForm,
    WpPostMedia,
    CompletionScreen,
    ActivityMedia,
    "youtube-media": YouTubeMedia,
  },
  props: {
    nodeId: {
      type: [Number, String],
      required: true,
    },
    dimensions: {
      type: Object,
      required: true,
    },
    context: {
      type: String,
      required: false,
      default: "lightbox",
    },
    autoplay: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  data() {
    return {
      showCompletionScreen: false,
      timeSinceLastSaved: new Date(),
    }
  },
  computed: {
    ...mapGetters(["getNode"]),
    node() {
      return this.getNode(this.nodeId)
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
    handleFormSubmit() {
      this.showCompletionScreen = true
      this.complete()
    },
    handleLoad(args) {
      this.$emit("load", args)
    },
    updateProgress(amountViewed) {
      this.updateNodeProgress({ id: this.nodeId, progress: amountViewed })
    },
    complete() {
      this.$emit("complete")
    },
    showTitle() {
      if (this.node.mediaType === "text") {
        return (
          this.context !== "page" ||
          (this.context === "page" && this.node.typeData.showTitle !== false)
        )
      } else if (this.node.mediaType === "wp-post") {
        return this.context === "page" && this.node.typeData.showTitle !== false
      } else {
        return (
          this.context === "slideshow" ||
          (this.context === "page" && this.node.typeData.showTitle !== false)
        )
      }
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
