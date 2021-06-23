<template>
  <div>
    <h1 v-if="showTitle" class="video-title">{{ node.title }}</h1>
    <div
      :style="{
        height: `${dimensions.height}px`,
        width: '100%',
      }"
    >
      <loading v-if="state === states.Loading" style="color: white;" />
      <component
        :is="videoComponent"
        ref="video"
        :style="{ opacity: showVideo ? 1 : 0 }"
        :node="node"
        :dimensions="dimensions"
        :playing="state === states.Playing"
        :context="context"
        @change:dimensions="$emit('change:dimensions', $event)"
        @complete="$emit('complete', nodeId)"
        @close="$emit('close')"
        @load="transition(events.Load, $event)"
        @play="transition(events.Play)"
        @pause="transition(events.Pause)"
        @timeupdate="transition(events.Timeupdate, $event)"
        @seeked="handleSeek"
      />
      <div v-if="state === states.Popup" class="popup" :style="popupStyle">
        <tapestry-media
          v-if="getNode(activePopupId).mediaType !== 'multi-content'"
          :dimensions="dimensions"
          :node-id="activePopupId"
          :context="context"
          :autoplay="autoplay"
          @complete="handlePopupComplete"
          @close="transition(events.Continue)"
        />
        <multi-content-media
          v-if="getNode(activePopupId).mediaType === 'multi-content'"
          :dimensions="dimensions"
          :context="context"
          :node="getNode(activePopupId)"
          @complete="handlePopupComplete"
          @close="transition(events.Continue)"
        />
      </div>
      <div v-if="completing" class="aside">
        <b-spinner></b-spinner>
      </div>
      <button
        v-else-if="isPopupComplete"
        class="aside"
        @click="transition(events.Continue)"
      >
        Continue
      </button>
      <play-screen
        v-if="state === states.Paused && showPlayScreen"
        class="screen"
        :hide-video="hideVideo"
        @play="transition(events.Play)"
      />
      <end-screen
        v-if="state === states.Finished"
        class="screen"
        :node="node"
        @rewatch="transition(events.Rewatch)"
        @close="transition(events.Close)"
      />
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex"

import UrlVideoMedia from "./UrlVideoMedia"
import H5PVideoMedia from "./H5PVideoMedia"
import YouTubeMedia from "./YouTubeMedia"
import EndScreen from "../common/EndScreen"
import PlayScreen from "../common/PlayScreen"
import { COMPLETION_THRESHOLD } from "./video.config"
import Loading from "@/components/common/Loading"
import client from "@/services/TapestryAPI"

/**
 * Video states and events as defined by the state machine diagram on Notion.
 * See:
 *   - https://www.notion.so/tapestrytool/977-Add-ability-to-create-timed-pop-up-for-Videos-1cda32c4c0e0486f8a20936bdabc0990#1212f47603534300a92aea2d59c5a248
 */
const VideoStates = {
  Loading: "loading",
  Paused: "paused",
  Playing: "playing",
  Finished: "finished",
  Popup: "popup",
  H5P: "h5p",
}

const VideoEvents = {
  Load: "load",
  Play: "play",
  Pause: "pause",
  Timeupdate: "timeupdate",
  Seeked: "seeked",
  Rewatch: "rewatch",
  Continue: "continue",
  Close: "close",
}

export default {
  components: {
    TapestryMedia: () => import("../TapestryMedia"),
    "youtube-media": YouTubeMedia,
    "h5p-video-media": H5PVideoMedia,
    UrlVideoMedia,
    EndScreen,
    PlayScreen,
    Loading,
    MultiContentMedia: () => import("../MultiContentMedia/index"),
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
    autoplay: {
      type: Boolean,
      required: true,
    },
    context: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      state: VideoStates.Loading,
      hideVideo: false,
      activePopupId: null,
      /**
       * Completing a node is done asynchronously, and we want to show a small
       * spinner on the bottom right of the node when this is currently in progress.
       */
      completing: false,
    }
  },
  computed: {
    ...mapGetters(["getNode", "getDirectChildren"]),
    states() {
      return VideoStates
    },
    events() {
      return VideoEvents
    },
    node() {
      return this.getNode(this.nodeId)
    },
    videoComponent() {
      switch (this.node.mediaFormat) {
        case "mp4":
          return "url-video-media"
        case "youtube":
          return "youtube-media"
        case "h5p":
          return "h5p-video-media"
        default:
          throw new Error(`Unknown video type: ${this.node.mediaFormat}`)
      }
    },
    popups() {
      const popups = this.getDirectChildren(this.node.id)
        .map(this.getNode)
        .filter(child => child.popup != null)
        .map(child => ({ time: child.popup.time, id: child.id }))
      popups.sort((a, b) => a.time - b.time)
      return popups
    },
    isPopupComplete() {
      const popup = this.getNode(this.activePopupId)
      if (popup) {
        return popup.completed
      }
      return false
    },
    popupStyle() {
      return this.isPopupComplete ? "height: calc(100% - 80px)" : ""
    },
    showVideo() {
      return [VideoStates.H5P, VideoStates.Paused, VideoStates.Playing].includes(
        this.state
      )
    },
    showPlayScreen() {
      return this.node.mediaType !== "h5p"
    },
    showTitle() {
      return this.context === "page" && this.node.typeData.showTitle !== false
    },
  },
  watch: {
    /**
     * Since the completing function is done in the parent lightbox container, the
     * only way we know if the popup is successfully completed is if the
     * `isPopupComplete` computed property changes.
     */
    isPopupComplete(isComplete) {
      if (isComplete && this.completing) {
        this.completing = false
      }
    },
  },
  methods: {
    /**
     * This function calculates the next state given the current state and the event
     * name, as well as perform any necessary side effects.
     */
    transition(eventName, context) {
      switch (this.state) {
        case VideoStates.Loading: {
          switch (eventName) {
            case VideoEvents.Load: {
              if (context && "currentTime" in context) {
                this.lastTime = context.currentTime
                this.state = this.getLoadState()

                if (this.state === VideoStates.Playing) {
                  client.recordAnalyticsEvent(
                    "app",
                    "auto-play",
                    context.type,
                    this.node.id,
                    {
                      time: context.currentTime,
                    }
                  )
                }
              } else {
                this.state = VideoStates.H5P
              }
              this.$emit("load", context)
              break
            }
          }
          break
        }
        case VideoStates.Paused: {
          switch (eventName) {
            case VideoEvents.Play: {
              this.state = VideoStates.Playing
              break
            }
          }
          break
        }
        case VideoStates.Playing: {
          switch (eventName) {
            case VideoEvents.Pause: {
              this.state = VideoStates.Paused
              break
            }
            case VideoEvents.Timeupdate: {
              const { amountViewed, currentTime } = context
              const activePopup = this.popups.find(
                popup =>
                  popup.time > (this.lastTime || 0) && popup.time < currentTime
              )
              if (activePopup) {
                this.state = VideoStates.Popup
                this.activePopupId = activePopup.id
              } else {
                if (amountViewed >= COMPLETION_THRESHOLD) {
                  this.$emit("complete")
                }

                // End of video
                if (amountViewed >= 1) {
                  this.state = VideoStates.Finished
                }
              }

              this.lastTime = currentTime
              this.$emit("timeupdate", { amountViewed, currentTime })
              break
            }
          }
          break
        }
        case VideoStates.Popup: {
          switch (eventName) {
            case VideoEvents.Continue:
              this.state = VideoStates.Playing
              this.activePopupId = null
              this.completing = false
          }
          break
        }
        case VideoStates.Finished: {
          switch (eventName) {
            case VideoEvents.Rewatch: {
              this.$refs.video.reset()
              this.state = VideoStates.Playing
              break
            }
            case VideoEvents.Close: {
              this.$emit("close")
            }
          }
          break
        }
      }
      this.$emit(eventName, context)
    },
    /**
     * We uniquely handle seek events because they don't trigger any state
     * transitions, and can occur at any video state.
     */
    handleSeek({ currentTime }) {
      this.lastTime = currentTime
    },
    getLoadState() {
      if (this.node.progress < 1) {
        return this.autoplay ? VideoStates.Playing : VideoStates.Paused
      }
      return VideoStates.Finished
    },
    handlePopupComplete() {
      if (!this.isPopupComplete) {
        this.completing = true
      }
      this.$emit("complete", this.activePopupId)
    },
  },
}
</script>

<style scoped lang="scss">
.video-title {
  text-align: left;
  margin-bottom: 0.9em;
  font-weight: 500;
  font-size: 1.75rem;

  :before {
    display: none;
  }
}

div {
  height: 100%;
  position: relative;
}

.aside {
  height: auto;
  position: absolute;
  border-radius: 0.5rem;
  right: 1rem;
  bottom: 1rem;
  z-index: 50;
}

button {
  &:hover {
    background: var(--tapestry-light-gray);
  }
}

.screen {
  border-radius: 15px;
}

.popup {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  transition: height 0.5s;
}
</style>
