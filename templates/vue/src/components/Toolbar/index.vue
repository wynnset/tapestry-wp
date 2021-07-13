<template>
  <div class="toolbar">
    <tapestry-filter v-if="!showMap" style="z-index: 10;" />
    <div v-show="canEdit || (!showMap && hasDepth)" class="slider-wrapper">
      <help-button v-if="canEdit"/>
      <review-notifications v-if="canEdit && settings.submitNodesEnabled" />
      <settings-modal-button
        v-if="canEdit"
        :max-depth="maxDepth"
      ></settings-modal-button>
      <tapestry-depth-slider
        v-show="!showMap && hasDepth"
        @change="updateViewBox"
        @change:max-depth="maxDepth = $event"
      ></tapestry-depth-slider>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapState } from "vuex"
import TapestryDepthSlider from "./TapestryDepthSlider"
import SettingsModalButton from "./SettingsModalButton"
import TapestryFilter from "./TapestryFilter"
import ReviewNotifications from "./ReviewNotifications"
import HelpButton from "./HelpButton"
import * as wp from "@/services/wp"

export default {
  components: {
    TapestryDepthSlider,
    TapestryFilter,
    SettingsModalButton,
    ReviewNotifications,
    HelpButton,
  },
  data() {
    return {
      maxDepth: 0,
    }
  },
  computed: {
    ...mapState(["nodes", "links", "selection", "settings", "rootId"]),
    canEdit() {
      return wp.canEditTapestry()
    },
    hasDepth() {
      return this.maxDepth > 1 && this.settings.defaultDepth > 0
    },
    showMap() {
      return this.settings.renderMap
    },
  },
  methods: {
    ...mapMutations(["select", "unselect", "clearSelection"]),
    updateViewBox() {
      this.$parent.updateViewBox()
    },
    getNodeDimensions() {
      this.$parent.getNodeDimensions()
    },
  },
}
</script>

<style>
.toolbar {
  display: flex;
  justify-content: space-between;
  padding: 0 5vw;
  transition: all 0.2s ease-out;
}
.slider-wrapper {
  background: #fbfbfb;
  box-shadow: 0 0 7px 0 #ddd;
  display: flex;
  align-items: center;
  border-radius: 4px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
  padding: 8px 6px 8px 12px;
  margin-left: auto;
  position: relative;
}
</style>
