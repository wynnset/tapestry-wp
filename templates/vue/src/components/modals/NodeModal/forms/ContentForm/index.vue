<template>
  <div id="modal-content-details">
    <b-form-group label="Title">
      <b-form-input
        id="node-title"
        v-model="node.title"
        data-qa="node-title"
        data-testid="node-title"
        placeholder="Enter title"
        autofocus
        required
      />
      <b-form-checkbox
        v-if="isMultiContentChild"
        v-model="shouldShowTitle"
        class="small title-checkbox"
        data-qa="node-show-page-title"
      >
        Show title in page
      </b-form-checkbox>
    </b-form-group>
    <div v-if="isMultiContentChild">
      <b-form-group
        v-if="addMenuTitle || node.typeData.menuTitle"
        label="Custom Menu Title"
      >
        <b-form-input
          id="node-nav-title"
          v-model="node.typeData.menuTitle"
          data-qa="node-nav-title"
          placeholder="Enter custom menu title"
          autofocus
        />
      </b-form-group>
      <div v-else class="text-right mt-n3 mb-2">
        <a href="#" class="small" @click="addMenuTitle = true">
          Add Custom Menu Title
        </a>
      </div>
    </div>
    <b-form-group v-if="addDesc || node.description.length" label="Description">
      <rich-text-form
        id="node-description"
        v-model="node.description"
        data-qa="node-description"
        placeholder="Enter description"
        :maxLength="maxDescriptionLength"
      />
    </b-form-group>
    <div v-else class="text-right mt-n3 mb-n2">
      <a href="#" class="small" @click="addDesc = true">Add Description</a>
    </div>
    <b-form-group v-if="node.hasSubAccordion" label="Subaccordion Text">
      <b-form-input v-model="node.typeData.subAccordionText"></b-form-input>
    </b-form-group>
    <b-form-group v-if="isPopupCandidate" label="Popup">
      <popup-form :node="node" />
    </b-form-group>
    <b-form-group label="Content Type">
      <b-form-select
        id="node-media-type"
        data-qa="node-media-type"
        data-testid="node-media-type"
        :value="node.mediaType"
        :options="mediaTypes"
        @change="handleTypeChange"
      ></b-form-select>
    </b-form-group>
    <b-form-group label="Content Details">
      <b-card
        bg-variant="light"
        class="px-4 py-3 pb-4 mx-n4 mb-n5"
        style="border-radius: 0; border-bottom:0;"
        no-body
      >
        <component
          :is="activeForm"
          v-if="activeForm"
          :node="node"
          :actionType="actionType"
          @load="$emit('load')"
          @unload="$emit('unload')"
        ></component>
      </b-card>
    </b-form-group>
  </div>
</template>

<script>
import { mapGetters } from "vuex"
import GravityFormsApi from "@/services/GravityFormsApi"
import ActivityForm from "./ActivityForm"
import MultiContentForm from "./MultiContentForm"
import GravityFormForm from "./GravityFormForm"
import H5pForm from "./H5pForm"
import PopupForm from "./PopupForm"
import TextForm from "./TextForm"
import RichTextForm from "./RichTextForm"
import UrlEmbedForm from "./UrlEmbedForm"
import VideoForm from "./VideoForm"
import WpPostForm from "./WpPostForm"

export default {
  components: {
    ActivityForm,
    MultiContentForm,
    GravityFormForm,
    H5pForm,
    PopupForm,
    TextForm,
    RichTextForm,
    UrlEmbedForm,
    VideoForm,
    WpPostForm,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
    parent: {
      type: Object,
      required: false,
      default: null,
    },
    actionType: {
      type: String,
      required: true,
    },
    maxDescriptionLength: {
      type: Number,
      required: false,
      default: 1000,
    },
  },
  data() {
    return {
      addDesc: false,
      mediaTypes: [
        { value: "", text: "Select content type" },
        { value: "text", text: "Text" },
        { value: "video", text: "Video" },
        { value: "h5p", text: "H5P" },
        { value: "url-embed", text: "External Link" },
        { value: "wp-post", text: "Wordpress Post" },
        { value: "activity", text: "Activity" },
        { value: "gravity-form", text: "Gravity Form", disabled: true },
        { value: "multi-content", text: "Multi-Content" }, // must be last item
      ],
      shouldShowTitle: this.node.typeData.showTitle !== false,
      addMenuTitle: false,
    }
  },
  computed: {
    ...mapGetters(["isMultiContentRow"]),
    activeForm() {
      return this.node.mediaType ? this.node.mediaType + "-form" : null
    },
    /**
     * If we're currently editing, a node is a popup candidate if its parent is a
     * video node AND it's not a popup itself AND is not part of a multi content.
     */
    isPopupCandidate() {
      // NOTE: Currently we do not want to allow a multi-content popup
      if (this.parent && this.node.mediaType !== "multi-content") {
        if (this.parent.popup || this.parent.isMultiContentChild) {
          return false
        }
        if (this.parent.mediaType === "h5p") {
          return this.parent.mediaDuration > 0
        }
        return this.parent.mediaType === "video"
      }
      return false
    },
    isMultiContentChild() {
      return (
        (this.parent && this.parent.mediaType === "multi-content") ||
        this.isMultiContentRow(this.node.id)
      )
    },
  },
  watch: {
    activeForm() {
      this.$emit("unload")
    },
    shouldShowTitle(shouldShowTitle) {
      this.node.typeData.showTitle = shouldShowTitle
    },
  },
  mounted() {
    GravityFormsApi.exists().then(exists => {
      const i = this.mediaTypes.findIndex(
        mediaType => mediaType.value == "gravity-form"
      )
      if (exists) {
        this.mediaTypes[i].disabled = false
      } else {
        this.mediaTypes[i].text += " (plugin unavailable)"
      }
    })
  },
  methods: {
    handleTypeChange(evt) {
      this.node.mediaType = evt
      this.node.mediaFormat = ""
      if (evt === "video" || evt === "h5p") {
        this.node.mediaFormat = evt === "video" ? "mp4" : "h5p"
      }
      this.$emit("type-changed", evt)
    },
  },
}
</script>

<style lang="scss">
.title-checkbox {
  padding-top: 3px;
  text-align: right;
}
</style>
