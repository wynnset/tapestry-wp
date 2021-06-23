<template>
  <div class="embed-responsive embed-responsive-16by9">
    <youtube
      class="embed-responsive-item"
      :video-id="node.typeData.youtubeID"
      :player-width="dimensions.width - 15"
      :player-height="dimensions.height - 40"
      :player-vars="{
        autoplay: 0,
        modestbranding: 1,
        rel: 0,
        iv_load_policy: 3,
        enablejsapi: 1,
      }"
      @ready="ready"
      @paused="handlePause"
      @playing="handlePlay"
      @ended="handleEnd"
    />
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex"
import Helpers from "@/utils/Helpers"
import client from "@/services/TapestryAPI"

import { SEEK_THRESHOLD } from "./video.config"

export default {
  name: "video-media",
  props: {
    node: {
      type: Object,
      required: true,
    },
    playing: {
      type: Boolean,
      required: true,
    },
    dimensions: {
      type: Object,
      required: true,
      validator: val => {
        return ["width", "height"].every(prop => val.hasOwnProperty(prop))
      },
    },
    context: {
      type: String,
      required: false,
      default: "",
    },
  },
  data() {
    return {
      videoDimensions: null,
      player: null,
    }
  },
  computed: {
    ...mapState(["h5pSettings"]),
    progress() {
      if (this.node.progress) {
        return this.node.progress
      } else if (this.node.typeData.progress) {
        return this.node.typeData.progress[0].value
      }
      return 0
    },
    showTitle() {
      return this.context === "page" && this.node.typeData.showTitle !== false
    },
    isMultiContentContext() {
      return this.context === "page" || this.context === "multi-content"
    },
  },
  watch: {
    playing(playing) {
      if (playing) {
        this.player.playVideo()
        this.startInterval()
      } else {
        this.stopInterval()
        this.player.pauseVideo()
      }
    },
  },
  beforeDestroy() {
    if (this.player) {
      this.player.stopVideo()
      this.updateVideoProgress()
      this.updateSettings()
    }
  },
  methods: {
    ...mapActions(["updateH5pSettings"]),
    ready(event) {
      this.player = event.target

      const currentTime = this.progress * this.player.getDuration()
      this.lastTime = currentTime
      this.player.seekTo(currentTime, true)
      this.applySettings()

      this.$emit("load", { currentTime })
    },
    reset() {
      this.player.seekTo(0, true)
      setTimeout(() => this.$emit("play"), 200)
    },
    close() {
      this.updateVideoProgress()
      this.updateSettings()
      this.$emit("close")
    },
    updateVideoProgress(ended = false) {
      if (this.player) {
        const currentTime = this.player.getCurrentTime()
        const duration = this.player.getDuration()

        if (Math.abs(currentTime - this.lastTime) > SEEK_THRESHOLD) {
          this.$emit("seeked", { currentTime })
          setTimeout(() => this.$emit("play"), 200)
        } else {
          this.$emit("timeupdate", {
            amountViewed: ended ? 1 : currentTime / duration,
            currentTime: ended ? duration : currentTime,
          })
        }
        this.lastTime = currentTime
      }
    },
    handlePause() {
      this.updateVideoProgress()
      this.updateSettings()
      const { id, progress, mediaDuration } = this.node
      client.recordAnalyticsEvent("user", "pause", "yt-video", id, {
        time: progress * mediaDuration,
      })
      this.$emit("pause")
    },
    handlePlay() {
      const { id, progress, mediaDuration } = this.node
      client.recordAnalyticsEvent("user", "play", "yt-video", id, {
        time: progress * mediaDuration,
      })
      this.$emit("play")
    },
    /**
     * The YouTube API doesn't expose a `timeupdate` event, so we have to track
     * our own updates by setting an interval.
     */
    startInterval() {
      if (this.interval) {
        clearInterval(this.interval)
      }
      this.interval = setInterval(() => this.updateVideoProgress(), 200)
    },
    stopInterval() {
      if (this.interval) {
        clearInterval(this.interval)
      }
    },
    handleEnd() {
      // Video current time may be a few milliseconds short and so won't mark it as complete
      this.updateVideoProgress(true)
      this.updateSettings()
    },
    applySettings() {
      if (!this.h5pSettings || Object.keys(this.h5pSettings).length === 0) return
      this.h5pSettings.muted ? this.player.mute() : this.player.unMute()
      this.h5pSettings.volume
        ? this.player.setVolume(this.h5pSettings.volume)
        : false
      this.h5pSettings.playbackRate
        ? this.player.setPlaybackRate(this.h5pSettings.playbackRate)
        : false
    },
    updateSettings() {
      const newSettings = { ...this.h5pSettings }
      newSettings.muted = this.player.isMuted()
      newSettings.volume = this.player.getVolume()
      newSettings.playbackRate = this.player.getPlaybackRate()
      if (Helpers.isDifferent(this.h5pSettings, newSettings)) {
        this.updateH5pSettings(newSettings)
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.embed-responsive {
  max-height: calc(100vh - 120px);
  height: 100%;
}

.video-title {
  text-align: left;
  margin-bottom: 0.9em;
  font-weight: 500;
  font-size: 1.75rem;

  :before {
    display: none;
  }
}
</style>
