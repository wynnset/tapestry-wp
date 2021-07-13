<template>
  <div id="modal-appearance">
    <h6 class="mb-3">Node Appearance</h6>
    <b-card class="px-3 pt-3" bg-variant="light" no-body>
      <b-container fluid class="bv-example-row">
        <b-row>
          <b-col lg class="pl-0">
            <h6 class="mb-3">Node Background</h6>
            <color-picker
              label="Background color"
              :currentColor="node.backgroundColor"
              :data-qa="`node-backgroundcolor-${node.id}`"
              @change="handleBackgroundColorChange"
            />
            <b-form-group>
              <b-form-checkbox
                v-model="addThumbnail"
                data-qa="node-appearance-thumbnail"
              >
                Background image
              </b-form-checkbox>
            </b-form-group>
            <b-form-group v-if="addThumbnail">
              <file-upload
                v-model="node.imageURL"
                input-test-id="node-appearance-thumbnail-url"
                :show-url-upload="false"
                thumbnail-type="thumbnail"
                :show-image-preview="true"
                file-types="image/*"
                @isUploading="handleUploadChange"
              />
            </b-form-group>
            <b-form-group v-if="addThumbnail || addLockedThumbnail">
              <b-form-checkbox
                v-model="addLockedThumbnail"
                data-qa="node-appearance-lockedThumbnail"
              >
                Show a different background when locked
              </b-form-checkbox>
            </b-form-group>
            <b-form-group v-if="addLockedThumbnail">
              <file-upload
                v-model="node.lockedImageURL"
                input-test-id="node-appearance-lockedThumbnail-url"
                :show-url-upload="false"
                thumbnail-type="locked"
                :show-image-preview="true"
                file-types="image/*"
                @isUploading="handleUploadChange"
              />
            </b-form-group>
          </b-col>
          <b-col lg class="pr-0">
            <h6 class="mb-3">Node Title</h6>
            <color-picker
              label="Text color"
              :currentColor="node.textColor"
              :data-qa="`node-textcolor-${node.id}`"
              @change="handleTextColorChange"
            />
            <b-form-group>
              <b-form-checkbox
                v-model="node.hideTitle"
                data-qa="node-appearance-title"
              >
                Hide title
              </b-form-checkbox>
            </b-form-group>
            <b-badge
              v-if="!node.imageURL"
              class="readability-badge"
              :variant="readabilityVariant"
            >
              Readability: {{ readability }}%
            </b-badge>
          </b-col>
        </b-row>
        <b-row>
          <b-col lg class="pl-0">
            <h6 class="mb-3">Other</h6>
            <b-form-group>
              <b-form-checkbox
                v-model="node.hideProgress"
                data-qa="node-appearance-progress"
              >
                Hide progress bar
              </b-form-checkbox>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox
                v-model="node.hideMedia"
                data-qa="node-appearance-media"
              >
                Hide media button
              </b-form-checkbox>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox
                v-show="hasPageMultiContentParent"
                v-model="node.typeData.isSecondaryNode"
                data-qa="node-appearance-secondary-node"
              >
                Is secondary node
              </b-form-checkbox>
            </b-form-group>
          </b-col>
        </b-row>
      </b-container>
    </b-card>
    <h6 class="mt-4 mb-3">Content Appearance</h6>
    <b-card class="px-3 pt-3" bg-variant="light" no-body>
      <b-form-group>
        <b-form-checkbox
          v-model="node.fullscreen"
          data-qa="node-behaviour-fullscreen"
          @input="node.fitWindow = true"
        >
          Open content in fullscreen
        </b-form-checkbox>
      </b-form-group>
      <b-form-group v-if="node.presentationStyle === 'page'">
        <b-form-checkbox
          :checked="
            node.typeData.hasOwnProperty('showNavBar')
              ? node.typeData.showNavBar
              : true
          "
          data-qa="nav-checkbox"
          @change="node.typeData.showNavBar = $event"
        >
          Add navigation bar
        </b-form-checkbox>
      </b-form-group>
      <b-form-group
        v-if="
          node.fullscreen && (node.mediaType === 'video' || node.mediaType === 'h5p')
        "
        class="indented-options"
      >
        <b-form-radio v-model="node.fitWindow" name="fit-window" :value="true">
          Fit whole video in window
        </b-form-radio>
        <b-form-radio v-model="node.fitWindow" name="fit-window" :value="false">
          Crop video to fill window (not recommended)
        </b-form-radio>
      </b-form-group>
    </b-card>
  </div>
</template>

<script>
import { mapGetters } from "vuex"
import FileUpload from "@/components/modals/common/FileUpload"
import ColorPicker from "@/components/modals/common/ColorPicker"
import TinyColor from "tinycolor2"

export default {
  components: {
    FileUpload,
    ColorPicker,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      addThumbnail: false,
      addLockedThumbnail: false,
    }
  },
  computed: {
    ...mapGetters(["getParent", "getNode"]),
    readability() {
      return Math.round(
        (Math.log(
          TinyColor.readability(this.node.textColor, this.node.backgroundColor)
        ) /
          Math.log(21)) *
          100
      )
    },
    readabilityVariant() {
      if (this.readability < 20) {
        return "danger"
      }
      if (this.readability < 40) {
        return "warning"
      }
      return "success"
    },
    hasPageMultiContentParent() {
      let parentNode = this.getNode(this.getParent(this.node.id))
      if (parentNode) {
        return (
          parentNode.mediaType === "multi-content" &&
          parentNode.presentationStyle === "page"
        )
      }
      return false
    },
  },
  watch: {
    addThumbnail(addThumbnail) {
      if (!addThumbnail) {
        this.node.imageURL = ""
        this.node.thumbnailFileId = ""
      }
    },
    addLockedThumbnail(addLockedThumbnail) {
      if (!addLockedThumbnail) {
        this.node.lockedImageURL = ""
        this.node.lockedThumbnailFileId = ""
      }
    },
  },
  created() {
    this.addThumbnail = this.node.imageURL && this.node.imageURL.length > 0
    this.addLockedThumbnail =
      this.node.lockedImageURL && this.node.lockedImageURL.length > 0
  },
  methods: {
    handleUploadChange(state) {
      this.$root.$emit("node-modal::uploading", state)
    },
    handleBackgroundColorChange(color) {
      this.node.backgroundColor = color
    },
    handleTextColorChange(color) {
      this.node.textColor = color
    },
  },
}
</script>

<style lang="scss" scoped>
.readability-badge {
  font-size: 0.9em;
  font-weight: normal;
  margin-bottom: 1em;
}
</style>
