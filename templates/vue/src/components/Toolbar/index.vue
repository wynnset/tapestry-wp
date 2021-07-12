<template>
  <div class="toolbar">
    <tapestry-filter v-if="!showMap" style="z-index: 10;" />
    <div
      v-show="isLoggedIn"
      :class="[{ 'hide-toolbar': hideToolbar }, 'slider-wrapper']"
    >
      <user-settings-button
        v-if="avatarsEnabled"
        data-qa="user-settings-button"
      ></user-settings-button>
      <div v-show="canEdit || (!showMap && hasDepth)" class="can-edit">
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
  </div>
</template>

<script>
import { mapMutations, mapState } from "vuex"
import TapestryDepthSlider from "./TapestryDepthSlider"
import SettingsModalButton from "./SettingsModalButton"
import UserSettingsButton from "./UserSettingsButton"
import TapestryFilter from "./TapestryFilter"
import ReviewNotifications from "./ReviewNotifications"
import * as wp from "@/services/wp"

export default {
  components: {
    TapestryDepthSlider,
    TapestryFilter,
    SettingsModalButton,
    ReviewNotifications,
    UserSettingsButton,
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
    isLoggedIn() {
      return wp.isLoggedIn()
    },
    hasDepth() {
      return this.maxDepth > 1 && this.settings.defaultDepth > 0
    },
    showMap() {
      return this.settings.renderMap
    },
    avatarsEnabled() {
      return this.isLoggedIn && process.env.VUE_APP_AVATARS === "TRUE"
    },
    hideToolbar() {
      return !(
        this.avatarsEnabled ||
        this.canEdit ||
        (!this.showMap && this.hasDepth)
      )
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
  padding: 8px 6px 8px 6px;
  margin-left: auto;
  position: relative;
}

.can-edit {
  display: flex;
}

.hide-toolbar {
  display: none;
}
</style>
