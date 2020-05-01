<template>
  <b-modal
    id="node-modal-container"
    :title="modalTitle"
    size="lg"
    class="text-muted"
    scrollable
    body-class="p-0"
  >
    <div class="modal-header-row">
      <b-alert
        v-if="formErrors.length"
        id="tapestry-modal-form-errors"
        variant="danger"
        show
        v-html="formErrors"
      ></b-alert>
    </div>
    <b-container fluid class="px-0">
      <b-tabs card>
        <b-tab title="Content" active>
          <div id="modal-content-details">
            <b-form-group label="Title">
              <b-form-input
                id="node-title"
                v-model="node.title"
                data-testid="node-title"
                placeholder="Enter title"
                autofocus
                required
              />
            </b-form-group>
            <b-form-group label="Description">
              <b-form-textarea
                id="node-description"
                v-model="node.description"
                data-testid="node-description"
                placeholder="Enter description"
              ></b-form-textarea>
            </b-form-group>
            <tyde-type-input :node="node" :parent="parent" />
            <b-form-group v-if="hasSubAccordion" label="Subaccordion Text">
              <b-form-input v-model="node.typeData.subAccordionText"></b-form-input>
            </b-form-group>
            <b-form-group label="Content Type">
              <b-form-select
                id="node-media-type"
                data-testid="node-mediaType"
                :value="nodeType"
                :options="mediaTypes"
                @change="handleTypeChange"
              ></b-form-select>
            </b-form-group>
            <quiz-modal v-if="node.mediaType === 'activity'" :node="node" />
            <accordion-form v-if="node.mediaType === 'accordion'" :node="node" />
            <b-form-group v-show="node.mediaType === 'wp-post'" label="Post Name">
              <combobox
                v-model="node.typeData.mediaURL"
                item-text="title"
                item-value="id"
                empty-message="There are no Wordpress posts yet. Please add one in your WP dashboard."
                :options="wpPosts"
              >
                <template v-slot="slotProps">
                  <p>
                    <code>{{ slotProps.option.id }}</code>
                    {{ slotProps.option.title }}
                  </p>
                </template>
              </combobox>
            </b-form-group>
            <b-form-group v-show="node.mediaType === 'text'" label="Text content">
              <b-form-textarea
                id="node-text-content"
                v-model="node.typeData.textContent"
                data-testid="node-textContent"
                placeholder="Enter text here"
              ></b-form-textarea>
            </b-form-group>
            <b-form-group
              v-show="node.mediaType === 'video' && nodeType !== 'h5p'"
              :label="videoLabel"
            >
              <file-upload
                id="node-video-media-url"
                v-model="node.typeData.mediaURL"
                data-testid="node-videoUrl"
                placeholder="Enter URL for MP4 Video"
                required
              />
              <b-form-text v-if="showVideoDescription">
                This video should not include any screenshots of the stage layout.
              </b-form-text>
            </b-form-group>
            <b-form-group
              v-show="node.mediaType === 'video' && nodeType !== 'h5p'"
              label="Video Duration"
            >
              <b-form-input
                id="node-video-media-duration"
                v-model="node.mediaDuration"
                data-testid="node-videoDuration"
                placeholder="Enter duration (in seconds)"
                required
              />
            </b-form-group>
            <b-form-group v-show="nodeType === 'h5p'" :label="h5pLabel">
              <combobox
                v-model="selectedH5pContent"
                item-text="title"
                item-value="id"
                empty-message="There's no H5P content yet. Please add one in your WP dashboard."
                :options="h5pContentOptions"
              >
                <template v-slot="slotProps">
                  <p>
                    <code>{{ slotProps.option.id }}</code>
                    {{ slotProps.option.title }}
                  </p>
                </template>
              </combobox>
              <b-form-text v-if="showVideoDescription">
                This H5P should not include any screenshots of the stage layout.
              </b-form-text>
            </b-form-group>
            <b-form-group
              v-show="nodeType === 'h5p'"
              label="H5P Video Duration"
              description="This only applies to video H5P content"
            >
              <b-form-input
                id="node-h5p-media-duration"
                v-model="node.mediaDuration"
                data-testid="node-h5pDuration"
                placeholder="Enter duration (in seconds)"
                required
              />
            </b-form-group>
            <b-form-group
              v-show="node.mediaType === 'gravity-form'"
              label="Gravity Form"
            >
              <combobox
                v-model="selectedGravityFormContent"
                data-testid="combobox-gravity-form"
                item-text="title"
                item-value="id"
                empty-message="There are no forms available. Please add one in your WP dashboard."
                :options="gravityFormOptions"
              >
                <template v-slot="slotProps">
                  <p>
                    <code>{{ slotProps.option.id }}</code>
                    {{ slotProps.option.title }}
                  </p>
                </template>
              </combobox>
            </b-form-group>
            <b-form-group
              v-if="node.mediaType === 'url-embed'"
              label="External Link"
            >
              <file-upload
                v-model="node.typeData.mediaURL"
                data-testid="node-linkUrl"
                placeholder="Enter embed link (starting with http)"
              />
            </b-form-group>
            <b-form-group v-if="node.mediaType === 'url-embed'" label="Behaviour">
              <b-form-radio-group
                id="external-link-behaviour"
                v-model="node.behaviour"
              >
                <b-form-radio value="embed" data-testid="node-linkBehaviour-embed">
                  Embed in Tapestry
                </b-form-radio>
                <b-form-radio
                  value="new-window"
                  data-testid="node-linkBehaviour-new-window"
                >
                  Open in a New Window
                </b-form-radio>
              </b-form-radio-group>
            </b-form-group>
          </div>
        </b-tab>
        <b-tab title="Appearance">
          <div id="modal-appearance">
            <h6 class="mb-3 text-muted">Node Appearance</h6>
            <b-form-group>
              <b-form-checkbox
                v-model="addThumbnail"
                data-testid="node-appearance-add-thumbnail"
              >
                Add a thumbnail
              </b-form-checkbox>
            </b-form-group>
            <b-form-group v-if="addThumbnail">
              <file-upload
                v-model="node.imageURL"
                data-testid="node-imageUrl"
                placeholder="Enter the URL for the thumbnail"
              />
            </b-form-group>
            <b-form-group v-if="addThumbnail">
              <b-form-checkbox
                v-model="addLockedThumbnail"
                data-testid="node-appearance-add-locked-thumbnail"
              >
                Show a different thumbnail when locked
              </b-form-checkbox>
            </b-form-group>
            <b-form-group v-if="addThumbnail && addLockedThumbnail">
              <file-upload
                v-model="node.lockedImageURL"
                data-testid="node-lockedImageURL"
                placeholder="Enter the URL for the thumbnail"
              />
            </b-form-group>
            <b-form-group>
              <b-form-checkbox
                v-model="node.hideTitle"
                data-testid="node-appearance-hide-title"
              >
                Hide node title
              </b-form-checkbox>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox
                v-model="node.hideProgress"
                data-testid="node-appearance-hide-progress"
              >
                Hide progress bar
              </b-form-checkbox>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox
                v-model="node.hideMedia"
                data-testid="node-appearance-hide-media"
              >
                Hide media button
              </b-form-checkbox>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox v-model="node.showInBackpack">
                Show in backpack
              </b-form-checkbox>
            </b-form-group>
            <h6 class="mt-4 mb-3 text-muted">Content Appearance</h6>
            <b-form-group>
              <b-form-checkbox
                v-model="node.fullscreen"
                data-testid="node-behaviour-fullscreen"
              >
                Open content in fullscreen
              </b-form-checkbox>
            </b-form-group>
          </div>
        </b-tab>
        <b-tab
          v-if="
            node.mediaType === 'h5p' ||
              node.mediaType === 'video' ||
              node.mediaType === 'accordion'
          "
          title="Behaviour"
        >
          <div id="modal-behaviour">
            <b-form-group>
              <b-form-checkbox
                v-if="node.mediaType !== 'accordion'"
                v-model="node.skippable"
                data-testid="node-behaviour-skippable"
              >
                Allow skipping video if user has not watched at least once
              </b-form-checkbox>
            </b-form-group>
          </div>
        </b-tab>
        <b-tab title="Access">
          <h6 class="mb-3 text-muted">General Permissions</h6>
          <div id="modal-permissions">
            <b-table-simple class="text-center" striped responsive>
              <b-thead>
                <b-tr>
                  <b-th></b-th>
                  <b-th>Read</b-th>
                  <b-th>Add</b-th>
                  <b-th>Edit</b-th>
                </b-tr>
              </b-thead>
              <b-tbody>
                <b-tr
                  v-for="(value, rowName) in permissions"
                  :key="rowName"
                  :value="value"
                >
                  <b-th>{{ rowName }}</b-th>
                  <b-td>
                    <b-form-checkbox
                      v-model="node.permissions[rowName]"
                      value="read"
                      :disabled="isPermissionDisabled(rowName, 'read')"
                      :data-testid="`node-permissions-${rowName}-read`"
                      @change="updatePermissions($event, rowName, 'read')"
                    ></b-form-checkbox>
                  </b-td>
                  <b-td>
                    <b-form-checkbox
                      v-model="node.permissions[rowName]"
                      value="add"
                      :disabled="isPermissionDisabled(rowName, 'add')"
                      :data-testid="`node-permissions-${rowName}-add`"
                      @change="updatePermissions($event, rowName, 'add')"
                    ></b-form-checkbox>
                  </b-td>
                  <b-td>
                    <b-form-checkbox
                      v-model="node.permissions[rowName]"
                      value="edit"
                      :disabled="isPermissionDisabled(rowName, 'edit')"
                      :data-testid="`node-permissions-${rowName}-edit`"
                      @change="updatePermissions($event, rowName, 'edit')"
                    ></b-form-checkbox>
                  </b-td>
                </b-tr>
                <b-tr>
                  <b-td colspan="4">
                    <b-input-group>
                      <b-form-input
                        v-model="userId"
                        placeholder="Enter user ID"
                      ></b-form-input>
                      <b-button variant="secondary" @click="addUserPermissionRow()">
                        <span class="fas fa-plus mr-1"></span>
                        User
                      </b-button>
                    </b-input-group>
                  </b-td>
                </b-tr>
              </b-tbody>
            </b-table-simple>
          </div>
          <h6 class="mt-4 mb-3 text-muted">Lock Node</h6>
          <conditions-form :node="node" />
        </b-tab>
        <b-tab
          v-if="node.mediaType === 'h5p' || node.mediaType === 'video'"
          title="Quiz"
        >
          <quiz-modal :node="node" />
        </b-tab>
        <b-tab v-if="node.tydeType === tydeTypes.MODULE" title="Spaceship Part">
          <div id="modal-spaceship-icons">
            <h6 class="mb-3 text-muted">Planet View Icon</h6>
            <b-form-group label="Not earned">
              <file-upload
                v-model="node.typeData.planetViewNotEarnedIconUrl"
                placeholder="Enter link (starting with http)"
              />
            </b-form-group>
            <b-form-group label="Earned">
              <file-upload
                v-model="node.typeData.planetViewEarnedIconUrl"
                placeholder="Enter link (starting with http)"
              />
            </b-form-group>
            <h6 class="mb-3 text-muted">Spaceship Cockpit Image</h6>
            <b-form-group label="Not earned">
              <file-upload
                v-model="node.typeData.spaceshipPartNotEarnedIconUrl"
                placeholder="Enter link (starting with http)"
              />
            </b-form-group>
            <b-form-group label="Earned">
              <file-upload
                v-model="node.typeData.spaceshipPartEarnedIconUrl"
                placeholder="Enter link (starting with http)"
              />
            </b-form-group>
            <b-form-group label="Hover">
              <file-upload
                v-model="node.typeData.spaceshipPartHoverIconUrl"
                placeholder="Enter link (starting with http)"
              />
            </b-form-group>
            <h6 class="mb-3 text-muted">
              Spaceship Part Coordinates and Size in Cockpit
            </h6>
            <b-row id="node-spaceship-parts" class="mb-4">
              <b-col sm="5" class="pt-2">
                Distance from upper left-hand corner:
              </b-col>
              <b-col>
                <b-input-group
                  prepend="X: "
                  append="%"
                  label-for="node-spaceship-part-x"
                >
                  <b-form-input
                    id="node-spaceship-part-x"
                    v-model="node.typeData.spaceshipPartX"
                    placeholder="In Percentage (top left)"
                    type="number"
                    min="0"
                    max="100"
                  />
                </b-input-group>
              </b-col>
              <b-col>
                <b-input-group
                  prepend="Y: "
                  append="%"
                  label-for="node-spaceship-part-y"
                >
                  <b-form-input
                    id="node-spaceship-part-y"
                    v-model="node.typeData.spaceshipPartY"
                    placeholder="In Percentage (top left)"
                    type="number"
                    min="0"
                    max="100"
                  />
                </b-input-group>
              </b-col>
            </b-row>
            <b-row id="node-spaceship-parts">
              <b-col sm="5" class="pt-2">
                Dimensions of image:
              </b-col>
              <b-col>
                <b-input-group
                  prepend="Width: "
                  append="%"
                  label-for="node-spaceship-part-width"
                >
                  <b-form-input
                    id="node-spaceship-part-width"
                    v-model="node.typeData.spaceshipPartWidth"
                    placeholder="In percentage"
                    type="number"
                    min="0"
                    max="100"
                  />
                </b-input-group>
              </b-col>
              <b-col>
                <b-input-group
                  prepend="Height: "
                  append="%"
                  label-for="node-spaceship-part-height"
                >
                  <b-form-input
                    id="node-spaceship-part-height"
                    v-model="node.typeData.spaceshipPartHeight"
                    placeholder="In percentage"
                    type="number"
                    min="0"
                    max="100"
                  />
                </b-input-group>
              </b-col>
            </b-row>
          </div>
        </b-tab>
        <b-tab
          v-if="
            node.tydeType === tydeTypes.MODULE ||
              node.mediaType === 'accordion' ||
              hasSubAccordion
          "
          title="Ordering"
        >
          <div>
            <slick-list
              :value="node.childOrdering"
              lock-axis="y"
              @input="updateOrderingArray"
            >
              <slick-item
                v-for="(id, index) in node.childOrdering"
                :key="index"
                class="slick-list-item"
                :index="index"
                style="z-index: 9999 !important;"
              >
                <span class="fas fa-bars fa-xs"></span>
                <span>{{ getNode(id).title }}</span>
                <span style="color: grey;">id: {{ id }}</span>
              </slick-item>
            </slick-list>
          </div>
        </b-tab>
      </b-tabs>
    </b-container>
    <template slot="modal-footer">
      <b-button
        v-show="modalType === 'edit-node'"
        size="sm"
        variant="danger"
        :disabled="disableDeleteButton"
        @click="$emit('delete-node')"
      >
        Delete Node
      </b-button>
      <p v-if="disableDeleteButton" class="disable-message text-muted">
        You cannot delete this node because this {{ node.tydeType }} node still has
        children.
      </p>
      <span style="flex-grow:1;"></span>
      <b-button size="sm" variant="secondary" @click="$emit('close-modal')">
        Cancel
      </b-button>
      <b-button size="sm" variant="primary" @click="submitNode()">
        Submit
      </b-button>
    </template>
  </b-modal>
</template>

<script>
import { mapGetters, mapMutations } from "vuex"
import Helpers from "../utils/Helpers"
import Combobox from "./Combobox"
import QuizModal from "./node-modal/QuizModal"
import FileUpload from "./FileUpload"
import H5PApi from "../services/H5PApi"
import { tydeTypes } from "../utils/constants"
import TydeTypeInput from "./node-modal/TydeTypeInput"
import WordpressApi from "../services/WordpressApi"
import GravityFormsApi from "../services/GravityFormsApi"
import AccordionForm from "./node-modal/AccordionForm"
import ConditionsForm from "./node-modal/ConditionsForm"
import { SlickList, SlickItem } from "vue-slicksort"

export default {
  name: "node-modal",
  components: {
    AccordionForm,
    Combobox,
    QuizModal,
    TydeTypeInput,
    ConditionsForm,
    FileUpload,
    SlickItem,
    SlickList,
  },
  props: {
    node: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    parent: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    modalType: {
      type: String,
      required: true,
      validator: function(value) {
        return (
          ["add-new-node", "edit-node", "add-root-node", ""].indexOf(value) !== -1
        )
      },
    },
    rootNodeTitle: {
      type: String,
      required: false,
      default: "Node",
    },
    permissionsOrder: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  data() {
    return {
      userId: null,
      mediaTypes: [
        { value: "", text: "Select content type" },
        { value: "text", text: "Text" },
        { value: "video", text: "Video" },
        { value: "h5p", text: "H5P" },
        { value: "url-embed", text: "External Link" },
        { value: "wp-post", text: "Wordpress Post" },
        { value: "gravity-form", text: "Gravity Form" },
        { value: "activity", text: "Activity" },
        { value: "accordion", text: "Accordion" },
      ],
      gravityFormOptions: [],
      h5pContentOptions: [],
      selectedGravityFormContent: "",
      selectedH5pContent: "",
      selectedWpPost: "",
      wpPosts: [],
      formErrors: "",
      maxDescriptionLength: 250,
      addThumbnail: false,
      addLockedThumbnail: false,
      tydeTypes: tydeTypes,
    }
  },
  computed: {
    ...mapGetters(["getDirectChildren", "getParent", "getNode"]),
    videoLabel() {
      const labels = {
        [tydeTypes.STAGE]: "Pre-Stage Video URL",
        [tydeTypes.MODULE]: "Module Completion Video URL",
      }
      return labels[this.node.tydeType] || "Video URL"
    },
    h5pLabel() {
      const labels = {
        [tydeTypes.STAGE]: "Pre-Stage H5P Content",
        [tydeTypes.MODULE]: "Module Completion H5P Content",
      }
      return labels[this.node.tydeType] || "H5P Content"
    },
    showVideoDescription() {
      return (
        this.node.tydeType === tydeTypes.STAGE ||
        this.node.tydeType === tydeTypes.MODULE
      )
    },
    hasChildren() {
      if (this.modalType === "edit-node") {
        return this.getDirectChildren(this.node.id).length > 0
      } else {
        return false
      }
    },
    disableDeleteButton() {
      if (
        this.node.tydeType === tydeTypes.MODULE ||
        this.node.tydeType === tydeTypes.STAGE
      ) {
        return this.hasChildren
      }
      return false
    },
    hasSubAccordion() {
      const parent = this.getParent(this.node)
      const children = this.getDirectChildren(this.node.id)
      return parent && parent.mediaType === "accordion" && children.length
    },
    nodeType() {
      if (this.node.mediaFormat === "h5p") {
        return "h5p"
      }
      return this.node.mediaType
    },
    modalTitle() {
      if (this.modalType === "add-new-node") {
        return `Add new sub-topic to ${this.rootNodeTitle}`
      } else if (this.modalType === "edit-node") {
        return `Edit node: ${this.rootNodeTitle}`
      } else if (this.modalType === "add-root-node") {
        return "Add root node"
      } else {
        return ""
      }
    },
    nodeData() {
      return [
        { name: "title", value: this.node.title },
        { name: "conditions", value: this.node.conditions || [] },
        { name: "description", value: this.node.description },
        { name: "behaviour", value: this.node.behaviour },
        { name: "mediaType", value: this.nodeType },
        {
          name: "mediaURL",
          value: this.getMediaUrl(),
        },
        {
          name: "textContent",
          value: this.node.typeData && this.node.typeData.textContent,
        },
        { name: "mediaDuration", value: this.node.mediaDuration },
        {
          name: "imageURL",
          value: this.node.imageURL || "",
        },
        {
          name: "lockedImageURL",
          value: this.node.lockedImageURL || "",
        },
        { name: "permissions", value: this.node.permissions },
        { name: "hideTitle", value: this.node.hideTitle },
        { name: "hideProgress", value: this.node.hideProgress },
        { name: "hideMedia", value: this.node.hideMedia },
        { name: "skippable", value: this.node.skippable },
        { name: "quiz", value: this.node.quiz || [] },
        { name: "fullscreen", value: this.node.fullscreen },
        { name: "tydeType", value: this.node.tydeType },
        { name: "showInBackpack", value: this.node.showInBackpack },
        {
          name: "planetViewNotEarnedIconUrl",
          value: this.node.typeData.planetViewNotEarnedIconUrl,
        },
        {
          name: "planetViewEarnedIconUrl",
          value: this.node.typeData.planetViewEarnedIconUrl,
        },
        {
          name: "spaceshipPartNotEarnedIconUrl",
          value: this.node.typeData.spaceshipPartNotEarnedIconUrl,
        },
        {
          name: "spaceshipPartEarnedIconUrl",
          value: this.node.typeData.spaceshipPartEarnedIconUrl,
        },
        {
          name: "spaceshipPartHoverIconUrl",
          value: this.node.typeData.spaceshipPartHoverIconUrl,
        },
        { name: "spaceshipPartX", value: this.node.typeData.spaceshipPartX },
        { name: "spaceshipPartY", value: this.node.typeData.spaceshipPartY },
        { name: "spaceshipPartWidth", value: this.node.typeData.spaceshipPartWidth },
        {
          name: "spaceshipPartHeight",
          value: this.node.typeData.spaceshipPartHeight,
        },
        { name: "subAccordionText", value: this.node.typeData.subAccordionText },
        { name: "childOrdering", value: this.node.childOrdering },
      ]
    },
    nodeImageUrl() {
      return this.node.imageURL
    },
    nodeLockedImageURL() {
      return this.node.lockedImageURL
    },
    newPermissions() {
      const last = this.permissionsOrder[this.permissionsOrder.length - 1]
      return [...this.node.permissions[last]]
    },
    permissions() {
      const ordered = {}
      this.permissionsOrder.forEach(permission => {
        ordered[permission] = this.node.permissions[permission]
      })
      return ordered
    },
  },
  watch: {
    nodeImageUrl() {
      this.addThumbnail = this.node.imageURL && this.node.imageURL.length > 0
    },
    nodeLockedImageURL() {
      this.addLockedThumbnail =
        this.node.lockedImageURL && this.node.lockedImageURL.length > 0
    },
    selectedH5pContent() {
      this.node.typeData.mediaURL = this.getMediaUrl()
    },
    selectedGravityFormContent(id) {
      this.node.typeData.mediaURL = id
    },
  },
  async mounted() {
    this.gravityFormOptions = await GravityFormsApi.getAllForms()
    this.h5pContentOptions = await H5PApi.getAllContent()
    this.wpPosts = await WordpressApi.getPosts()
    this.$root.$on("bv::modal::show", (bvEvent, modalId) => {
      if (modalId == "node-modal-container") {
        this.formErrors = ""
        thisTapestryTool.disableMovements()
      }
    })
    this.$root.$on("bv::modal::shown", (bvEvent, modalId) => {
      if (modalId == "node-modal-container") {
        this.setInitialTydeType()
        const selectedContent = this.h5pContentOptions.find(content =>
          this.filterContent(content)
        )
        if (this.node.mediaType === "gravity-form") {
          const selectedForm = this.gravityFormOptions.find(form => {
            return form.id === this.node.typeData.mediaURL
          })
          this.selectedGravityFormContent = selectedForm ? selectedForm.id : ""
        }
        this.selectedH5pContent = selectedContent ? selectedContent.id : ""
      }
    })
    this.$root.$on("bv::modal::hide", (_, modalId) => {
      if (modalId == "node-modal-container") {
        thisTapestryTool.enableMovements()
      }
    })
  },
  methods: {
    ...mapMutations(["updateOrdering"]),
    setInitialTydeType() {
      // only set node types if adding a new node
      if (this.parent && this.modalType === "add-new-node") {
        const parentType = this.parent.tydeType
        this.node.tydeType =
          parentType === tydeTypes.MODULE
            ? tydeTypes.STAGE
            : parentType === tydeTypes.STAGE
            ? tydeTypes.QUESTION_SET
            : tydeTypes.REGULAR
      }
    },
    filterContent(content) {
      if (this.node.mediaFormat !== "h5p") {
        return false
      }
      const id = this.node.typeData.mediaURL.split("&id=")[1]
      return content.id == id
    },
    getMediaUrl() {
      if (this.nodeType !== "h5p") {
        return this.node.typeData && this.node.typeData.mediaURL
      }

      const adminAjaxUrl = wpData.adminAjaxUrl
      return `${adminAjaxUrl}?action=h5p_embed&id=${this.selectedH5pContent}`
    },
    getPermissionRowIndex(rowName) {
      return this.permissionsOrder.findIndex(thisRow => thisRow === rowName)
    },
    isPermissionDisabled(rowName, type) {
      if (rowName == "public") {
        return false
      }

      // keep going up until we find a non-user higher row
      const rowIndex = this.getPermissionRowIndex(rowName)
      const higherRow = this.permissionsOrder[rowIndex - 1]
      if (higherRow.startsWith("user") || wpData.roles.hasOwnProperty(higherRow)) {
        return this.isPermissionDisabled(higherRow, type)
      }

      const permissions = this.node.permissions[higherRow]
      if (permissions) {
        return permissions.includes(type)
      }
      return false
    },
    changeIndividualPermission(value, rowName, type) {
      let currentPermissions = this.node.permissions[rowName]
      if (!currentPermissions) {
        currentPermissions = []
      }
      let newPermissions = [...currentPermissions]
      if (value) {
        if (!currentPermissions.includes(value)) {
          newPermissions.push(value)
        }
      } else {
        newPermissions = currentPermissions.filter(permission => permission !== type)
      }
      this.$set(this.node.permissions, rowName, newPermissions)
    },
    updatePermissions(value, rowName, type) {
      if (rowName.startsWith("user") || wpData.roles.hasOwnProperty(rowName)) {
        return this.changeIndividualPermission(value, rowName, type)
      }
      const rowIndex = this.getPermissionRowIndex(rowName)
      const lowerPriorityPermissions = this.permissionsOrder.slice(rowIndex + 1)
      lowerPriorityPermissions.forEach(newRow => {
        this.changeIndividualPermission(value, newRow, type)
      })
    },
    handleTypeChange(event) {
      this.$set(this.node, "mediaType", event)
      if (event === "video" || event === "h5p") {
        this.$set(this.node, "mediaFormat", event === "video" ? "mp4" : "h5p")
      } else {
        this.$set(this.node, "mediaFormat", "")
      }
    },
    submitNode() {
      this.formErrors = this.validateNode(this.nodeData)
      if (!this.formErrors.length) {
        if (this.modalType === "add-root-node") {
          this.$emit("add-edit-node", this.nodeData, false, true)
        } else if (this.modalType === "add-new-node") {
          this.$emit("add-edit-node", this.nodeData, false)
        } else if (this.modalType === "edit-node") {
          this.$emit("add-edit-node", this.nodeData, true)
        } else {
          console.error(`Undefined modalType: ${this.modalType}`)
        }
      }
    },
    validateNode() {
      var errMsgs = []

      if (this.node.title.length == 0) {
        errMsgs.push("Please enter a title")
      }
      if (this.node.description.length > this.maxDescriptionLength) {
        errMsgs.push(
          "Please limit your description to under " +
            this.maxDescriptionLength +
            " characters"
        )
      }

      const quiz = this.node.quiz
      if (!this.validateQuiz(quiz)) {
        errMsgs.push("Please enter at least one answer ID for each question")
      }

      if (!this.node.mediaType) {
        errMsgs.push("Please select a Content Type")
      } else if (this.node.mediaType === "video") {
        if (this.node.typeData.mediaURL === "") {
          errMsgs.push("Please enter a Video URL")
        }
        if (!Helpers.onlyContainsDigits(this.node.mediaDuration)) {
          errMsgs.push("Please enter numeric value for Video Duration")
        }
      } else if (this.node.mediaType === "h5p") {
        if (this.node.typeData.mediaURL === "") {
          errMsgs.push("Please select an H5P content for this node")
        }
        if (!Helpers.onlyContainsDigits(this.node.mediaDuration)) {
          errMsgs.push("Please enter numeric value for H5P Video Duration")
        }
      } else if (this.node.mediaType === "url-embed") {
        if (this.node.typeData.mediaURL === "") {
          errMsgs.push("Please enter an Embed URL")
        }
      } else if (this.node.mediaType === "text") {
        if (
          !this.node.typeData.textContent ||
          !this.node.typeData.textContent.length
        ) {
          errMsgs.push("Please enter Text Content for this node")
        }
      }

      return errMsgs.join("<br>")
    },
    validateQuiz(quiz) {
      return quiz.every(question => {
        return Object.values(question.answers).some(
          value => value && value.length > 0
        )
      })
    },
    addUserPermissionRow() {
      const userId = this.userId
      if (
        userId &&
        Helpers.onlyContainsDigits(userId) &&
        $("#user-" + userId + "-editcell").val() != ""
      ) {
        this.$set(this.node.permissions, `user-${userId}`, this.newPermissions)
        this.permissionsOrder.push(`user-${userId}`)
        this.userId = null
      } else {
        alert("Enter valid user id")
      }
    },
    updateOrderingArray(arr) {
      this.updateOrdering({
        id: this.node.id,
        ord: arr,
      })
    },
  },
}
</script>

<style lang="scss">
/* Use non-scoped styles to overwrite WP theme styles */
table {
  border: 1px solid #dee2e6;

  th,
  td {
    word-break: unset;
    border: none;
  }
}

/* overwrite bootstrap styles */
.modal-header {
  background: #f7f7f7;
  border: none;
  padding-bottom: 0;
  margin-left: 5px;
  flex-direction: column;

  button.close {
    position: absolute;
    top: 15px;
    right: 12px;

    &:focus {
      outline: none;
    }
  }
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
}

.nav-link:focus {
  outline: none;
}
</style>

<style lang="scss" scoped>
#node-modal-container {
  * {
    outline: none;
  }

  .disable-message {
    font-size: 0.9em;
    padding: 0;
    margin: 0 0 0 8px;
  }

  .modal-header-row {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 0;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .slick-list-item {
    display: flex;
    height: 25px;
    border: lightgray solid 1.5px;
    margin: 10px 25px;
    border-radius: 5px;
    padding: 15px;
    align-items: center;

    > span {
      margin-right: 25px;
    }

    > span:last-of-type {
      margin-left: auto;
    }
  }
}

.slick-list-item {
  display: flex;
  height: 25px;
  border: lightgray solid 1.5px;
  margin: 10px 25px;
  border-radius: 5px;
  padding: 15px;
  align-items: center;
  > span {
    margin-right: 25px;
  }
  > span:last-of-type {
    margin-left: auto;
  }
}
</style>
