<template>
  <div class="container">
    <div
      ref="h5pIframeContainer"
      class="h5p-iframe-container"
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
        @load="handleLoad"
      ></iframe>
    </div>
  </div>
</template>

<script>
import Helpers from "@/utils/Helpers"
import { SEEK_THRESHOLD } from "../video.config"
import { mapActions, mapState } from "vuex"
import client from "@/services/TapestryAPI"

export default {
  name: "h5p-video-media",
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
    playing: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      instance: null,
      library: null,
      frameHeight: null,
      frameWidth: null,
      refreshed: false,
    }
  },
  computed: {
    ...mapState(["h5pSettings"]),
    hasMultiContentContext() {
      return this.context === "multi-content" || this.context === "page"
    },
  },
  watch: {
    node(newNode, oldNode) {
      if (newNode.id !== oldNode.id) {
        this.handlePause(oldNode)
      }
    },
    playing(isPlaying) {
      const video = this.getInstance()
      if (isPlaying) {
        this.startTimeupdateHandler()
        video.play()
      } else {
        this.stopTimeupdateHandler()
        video.pause()
      }
    },
  },
  beforeDestroy() {
    window.removeEventListener("resize", this.setFrameDimensions)
    document.removeEventListener("fullscreenchange", this.setFrameDimensions)
    document.removeEventListener("webkitfullscreenchange", this.setFrameDimensions)
    document.removeEventListener("mozfullscreenchange", this.setFrameDimensions)
  },

  methods: {
    ...mapActions(["updateH5pSettings"]),
    startTimeupdateHandler() {
      this.stopTimeupdateHandler()
      this.interval = setInterval(() => this.updateVideoProgress(), 200)
    },
    stopTimeupdateHandler() {
      if (this.interval) {
        clearInterval(this.interval)
      }
    },
    updateVideoProgress() {
      const video = this.getInstance()

      /**
       * An H5P video is just one of the many types H5Ps can be, so we only want to
       * trigger this if the H5P is in fact a video.
       */
      if (video) {
        const currentTime = video.getCurrentTime()
        const duration = video.getDuration()

        if (Math.abs(currentTime - this.lastTime) > SEEK_THRESHOLD) {
          this.$emit("seeked", { currentTime })
        } else {
          this.$emit("timeupdate", {
            amountViewed: currentTime / duration,
            currentTime,
          })
        }

        this.lastTime = currentTime
        this.updateSettings(video)
      } else {
        this.stopTimeupdateHandler()
      }
    },
    setFrameDimensions() {
      const h5pDimensions = this.instance.parent.$container[0].getBoundingClientRect()

      // default
      this.frameHeight = h5pDimensions.height
      this.frameWidth = 0

      if (this.node.fitWindow || this.hasMultiContentContext) {
        // Video should fit within the smaller of the viewport or the container it's in
        let fitHeight = window.innerHeight
        if (this.hasMultiContentContext) {
          // Count for the header
          // TODO: Find a better way of doing this without hardcoding the heigh value
          fitHeight -= 100
        }
        // Proportionally make the frame smaller
        let scaleFactor = fitHeight / h5pDimensions.height
        this.frameHeight = h5pDimensions.height * scaleFactor
        this.frameWidth = h5pDimensions.width * scaleFactor

        // if the width is bigger than the available space, we need to scale based on the width
        let fitWidth = this.$refs.h5pIframeContainer.clientWidth
        if (this.frameWidth > fitWidth) {
          scaleFactor = fitWidth / h5pDimensions.width
          this.frameWidth = h5pDimensions.width * scaleFactor
          this.frameHeight = h5pDimensions.height * scaleFactor
        }
      }

      // Fix for unknown issue where H5P height is just a bit short
      if (this.frameHeight) {
        this.frameHeight += 2
      }

      let updatedDimensions = { height: this.frameHeight }
      if (this.frameWidth) {
        updatedDimensions.width = this.frameWidth
      }
      this.$emit("change:dimensions", updatedDimensions)
    },
    getInstance() {
      if (this.$refs.h5p) {
        const h5pObj = this.$refs.h5p.contentWindow.H5P
        return h5pObj.instances[0].video
      }
      return null
    },
    updateSettings(h5pVideo) {
      let newSettings = {}

      try {
        newSettings.volume = h5pVideo.getVolume()
      } catch (Error) {
        console.error("H5P volume not saved", Error)
      }

      try {
        newSettings.muted = h5pVideo.isMuted()
      } catch (Error) {
        console.error("H5P mute status not saved", Error)
      }

      try {
        newSettings.playbackRate = h5pVideo.getPlaybackRate()
      } catch (Error) {
        console.error("H5P playback rate not saved", Error)
      }

      try {
        newSettings.quality = h5pVideo.getQuality()
      } catch (Error) {
        console.error("H5P quality settings not saved", Error)
      }

      try {
        newSettings.caption = h5pVideo.getCaptionsTrack()
      } catch (Error) {
        /**
         * In H5P YouTube videos, the caption track can be undefined which leads to
         * this throwing. Since this function is called on timeupdate, the console
         * can get bloated quite quickly. Instead, if we fail to set a caption we're
         * going to default it to an empty object.
         */
        newSettings.caption = {}
      }

      if (Helpers.isDifferent(newSettings, this.h5pSettings)) {
        client.recordAnalyticsEvent(
          "user",
          "update-settings",
          "h5p-video",
          this.node.id,
          { from: this.h5pSettings, to: newSettings }
        )
        this.updateH5pSettings(newSettings)
      }
    },
    applySettings(h5pVideo) {
      const settings = this.h5pSettings
      if (settings.volume !== undefined) {
        h5pVideo.setVolume(settings.volume)
      }
      if (settings.muted !== undefined) {
        if (settings.muted) {
          h5pVideo.mute()
          this.toggleMuteIcon()
        } else {
          h5pVideo.unMute()
        }
      }
      if (settings.playbackRate !== undefined) {
        h5pVideo.setPlaybackRate(settings.playbackRate)
      }
      if (settings.quality !== undefined) {
        h5pVideo.setQuality(settings.quality)
      }
      if (settings.caption !== undefined) {
        h5pVideo.setCaptionsTrack(settings.caption)
      }
    },
    handlePlay() {
      const { id, progress, mediaDuration } = this.node
      client.recordAnalyticsEvent("user", "play", "h5p-video", id, {
        time: progress * mediaDuration,
      })
      this.$emit("play")
    },
    handlePause() {
      const video = this.getInstance()
      /**
       * When an H5PInteractiveVideo ends, it emits a Pause event BEFORE an Ended
       * event. This breaks our state machine since our machine doesn't allow
       * transitioning from a Paused state to a Finished state. As a work around,
       * we listen to the Pause event and manually check if the video is done at
       * this point. If it is, we emit the corresponding `timeupdate` event.
       */
      if (video.getCurrentTime() === video.getDuration()) {
        this.$emit("timeupdate", {
          amountViewed: 1,
          currentTime: video.getDuration(),
        })
      } else {
        const { id, progress, mediaDuration } = this.node
        client.recordAnalyticsEvent("user", "pause", "h5p-video", id, {
          time: progress * mediaDuration,
        })
        let hasDialogue = this.$refs.h5p.contentWindow.H5P.$body[0].querySelector(
          ".h5p-dialog-wrapper"
        )
        if (
          hasDialogue.style.display === "none" ||
          hasDialogue.style.display === ""
        ) {
          this.$emit("pause")
        }
      }
    },
    handleVideoLoad(currentTime) {
      this.$emit("load", {
        type: "h5p-video",
        currentTime,
      })
      this.$nextTick(() => {
        this.setFrameDimensions()
      })
    },
    handleLoad() {
      const h5pObj = this.$refs.h5p.contentWindow.H5P
      const h5pInstance = h5pObj.instances[0]
      const loadedH5PId = h5pInstance.contentId

      this.library = h5pInstance.libraryInfo.machineName

      // Check to see whether this is an H5P recorder
      // If it is, we can emit an event to load the recorded audio (if exists)
      // and terminate
      if (h5pInstance.recorder && loadedH5PId) {
        this.loadedH5PRecorderId = loadedH5PId
        this.h5pRecorderSaverIsLoaded()
        return
      }

      const mediaProgress = this.node.progress

      this.frameHeight = this.$refs.h5p.contentWindow.document.activeElement.children[0].clientHeight
      this.$emit("change:dimensions", { height: this.frameHeight })

      const h5pVideo = h5pInstance.video
      const h5pIframeComponent = this

      const handleH5pAfterLoad = () => {
        h5pIframeComponent.instance = h5pVideo
        window.addEventListener("resize", h5pIframeComponent.setFrameDimensions)
        document.addEventListener(
          "fullscreenchange",
          h5pIframeComponent.setFrameDimensions
        )
        document.addEventListener(
          "webkitfullscreenchange",
          h5pIframeComponent.setFrameDimensions
        )
        document.addEventListener(
          "mozfullscreenchange",
          h5pIframeComponent.setFrameDimensions
        )

        const videoDuration = h5pVideo.getDuration()
        h5pVideo.seek(mediaProgress * videoDuration)
        this.lastTime = mediaProgress * videoDuration

        h5pIframeComponent.applySettings(h5pVideo)

        /**
         * When a regular H5PInteractiveVideo (i.e. not created using a
         * YouTube link) loads, it goes through two state changes —
         * Playing, then Paused. If we emit a load event before these
         * state changes occur, a race condition can occur where the
         * node won't play even if autoplay is true.
         *
         * To work around this, we delay the load by some time to make room
         * for the pause event to occur.
         */
        setTimeout(() => {
          this.handleVideoLoad(mediaProgress * videoDuration)
        }, 500)

        h5pVideo.on("stateChange", event => {
          switch (event.data) {
            case h5pObj.Video.PLAYING: {
              this.handlePlay()
              break
            }

            case h5pObj.Video.PAUSED: {
              this.handlePause()
              break
            }
          }
        })
      }

      if (h5pVideo.getDuration() !== undefined) {
        /**
         * When an H5P video appears in an accordion, it can sometimes appear
         * cut off. To work around this, we refresh the h5p content window and
         * wait for that to load.
         */
        if (this.context === "accordion" && !this.refreshed) {
          this.$refs.h5p.contentWindow.location.reload()
          this.refreshed = true
        } else {
          handleH5pAfterLoad()
        }
      } else {
        h5pVideo.on("loaded", handleH5pAfterLoad)
      }

      this.$emit("load")
    },
    toggleMuteIcon() {
      const body = this.$refs.h5p.contentWindow.H5P.$body[0]
      const btn = body.querySelector(".h5p-mute")
      btn.classList.toggle("h5p-muted")
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
.h5p-iframe-container {
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
