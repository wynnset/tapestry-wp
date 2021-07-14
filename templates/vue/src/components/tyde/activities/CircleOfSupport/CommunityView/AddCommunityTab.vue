<template>
  <div style="width: 100%">
    <cos-popup
      :show="show"
      style="position: absolute; left: 0; bottom: 0; width: 100%;"
    >
      <template #toggle>
        <cos-popup-button
          id="community-tab-popup-trigger"
          style="right: 2rem"
          :disabled="!show && disabled"
          @click="togglePopup"
        >
          <tapestry-icon :icon="show ? 'chevron-down' : 'plus'" />
        </cos-popup-button>
      </template>
      <template #content>
        <div class="content-wrapper">
          <add-community-form
            ref="form"
            :community="community"
            @change="handleChange"
            @back="$emit('back')"
            @add-community="handleAddCommunity"
          />
        </div>
      </template>
    </cos-popup>
    <b-tooltip :disabled="show || !disabled" target="community-tab-popup-trigger">
      {{ tooltipText }}
    </b-tooltip>
    <cos-modal v-model="showModal">
      <div class="confirm">
        <h1>Would you like to add this community now or later?</h1>
        <ul class="controls">
          <li><button class="secondary" @click="close">Later</button></li>
          <li>
            <button class="primary" @click="addCommunityViaModal">
              Add it now
            </button>
          </li>
        </ul>
      </div>
    </cos-modal>
  </div>
</template>

<script>
import AddCommunityForm from "./AddCommunityForm"
import CosPopup from "./CosPopup"
import CosModal from "./CosModal"
import CosPopupButton from "./CosPopupButton"
import TapestryIcon from "@/components/common/TapestryIcon"
import { MAX_COMMUNITIES } from "../cos.config"

export default {
  components: {
    AddCommunityForm,
    CosModal,
    CosPopup,
    CosPopupButton,
    TapestryIcon,
  },
  model: {
    prop: "community",
    event: "change",
  },
  props: {
    community: {
      type: Object,
      required: true,
    },
    show: {
      type: Boolean,
      required: true,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      showModal: false,
      wasEdited: false,
    }
  },
  computed: {
    tooltipText() {
      return `Cannot add more communities (limit: ${MAX_COMMUNITIES}). Please delete a community before adding another.`
    },
  },
  methods: {
    handleChange(evt) {
      this.wasEdited = true
      this.$emit("change", evt)
    },
    togglePopup() {
      if (this.show && this.wasEdited) {
        this.showModal = true
      } else {
        this.$emit("toggle")
      }
    },
    close() {
      this.showModal = false
      this.wasEdited = false
      this.$emit("back")
    },
    addCommunityViaModal() {
      this.showModal = false
      this.$refs.form.addCommunity()
    },
    handleAddCommunity(community) {
      this.wasEdited = false
      this.$emit("back")
      this.$emit("add-community", community)
    },
  },
}
</script>

<style scoped lang="scss">
.confirm {
  display: flex;
  flex-direction: column;
  justify-content: space-between;

  h1 {
    color: #757575;
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
  }
}

.content-wrapper {
  background: white;
  position: relative;
  z-index: 10;
  height: 100%;
  border-top: 1px solid var(--cos-color-tertiary);
}

.controls {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  column-gap: 1rem;
  color: #757575;

  > * {
    flex: 1;
  }

  button {
    --border-color: var(--cos-color-secondary);

    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    color: inherit;
    border: 3px solid var(--border-color);
    border-radius: 0.5rem;

    &.primary {
      --border-color: transparent;
      background: #757575;
      color: white;
    }
  }
}
</style>
