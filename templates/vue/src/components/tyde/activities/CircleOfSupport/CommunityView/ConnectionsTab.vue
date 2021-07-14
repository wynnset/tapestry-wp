<template>
  <cos-popup :show="show">
    <template #toggle>
      <cos-popup-button
        style="left: 2rem"
        aria-label="Connections"
        @click="$emit('toggle')"
      >
        <tapestry-icon v-if="show" icon="chevron-down" />
        <span v-else>😊</span>
      </cos-popup-button>
    </template>
    <template #content>
      <slot></slot>
    </template>
  </cos-popup>
</template>

<script>
import TapestryIcon from "@/components/common/TapestryIcon"
import CosPopup from "./CosPopup"
import CosPopupButton from "./CosPopupButton"

export default {
  components: {
    CosPopup,
    CosPopupButton,
    TapestryIcon,
  },
  props: {
    show: {
      type: Boolean,
      required: true,
    },
  },
}
</script>
