<template>
  <b-modal
    v-if="node"
    id="node-modal"
    :visible="show"
    :title="title"
    size="lg"
    class="text-muted"
    scrollable
    body-class="p-0"
    @hidden="close"
  >
    <div v-if="formErrors.length" class="modal-header-row">
      <b-alert id="tapestry-modal-form-errors" variant="danger" show>
        <ul>
          <li v-for="error in formErrors" :key="error">{{ error }}</li>
        </ul>
      </b-alert>
    </div>
    <b-container v-if="loading" class="spinner">
      <b-spinner variant="secondary"></b-spinner>
    </b-container>
    <b-container v-else fluid class="px-0" data-qa="node-modal">
      <b-tabs card>
        <b-tab
          title="Content"
          :active="tab === 'content'"
          style="overflow-x: hidden;"
          @click="changeTab('content')"
        >
          <content-form
            :node="node"
            @load="videoLoaded = true"
            @unload="videoLoaded = false"
            @type-changed="handleTypeChange"
          />
        </b-tab>
        <b-tab
          title="Appearance"
          :active="tab === 'appearance'"
          @click="changeTab('appearance')"
        >
          <appearance-form :node="node" />
        </b-tab>
        <b-tab
          v-if="node.mediaType === 'h5p' || node.mediaType === 'video'"
          :active="tab === 'behaviour'"
          title="Behaviour"
          @click="changeTab('behaviour')"
        >
          <behaviour-form :node="node" />
        </b-tab>
        <b-tab
          v-if="viewAccess"
          title="Access"
          :active="tab === 'access'"
          @click="changeTab('access')"
        >
          <h6 class="mb-3">Node Permissions</h6>
          <b-card no-body>
            <permissions-table v-model="node.permissions" />
          </b-card>
          <h6 class="mt-4 mb-3">Lock Node</h6>
          <conditions-form :node="node" />
        </b-tab>
        <b-tab
          v-if="node.mediaType === 'h5p' || node.mediaType === 'video'"
          title="Activity"
          :active="tab === 'activity'"
          @click="changeTab('activity')"
        >
          <activity-form :node="node" />
        </b-tab>
        <b-tab
          v-if="node.mediaType === 'accordion' || node.hasSubAccordion"
          title="Ordering"
          :active="tab === 'ordering'"
          @click="changeTab('ordering')"
        >
          <div>
            <slick-list
              :value="node.childOrdering"
              lock-axis="y"
              @input="updateOrderingArray"
            >
              <slick-item
                v-for="(childId, index) in node.childOrdering"
                :key="index"
                class="slick-list-item"
                :index="index"
                style="z-index: 9999 !important;"
              >
                <span class="fas fa-bars fa-xs"></span>
                <span>{{ getNode(childId).title }}</span>
                <span style="color: grey;">id: {{ childId }}</span>
              </slick-item>
            </slick-list>
          </div>
        </b-tab>
        <b-tab
          title="More Information"
          :active="tab === 'more-information'"
          @click="changeTab('more-information')"
        >
          <more-information-form :node="node" />
        </b-tab>
      </b-tabs>
    </b-container>
    <template slot="modal-footer">
      <delete-node-button
        v-if="type === 'edit'"
        :node-id="Number(nodeId)"
        @submit="loading = true"
      ></delete-node-button>
      <span style="flex-grow:1;"></span>
      <b-button size="sm" variant="light" @click="close">
        Cancel
      </b-button>
      <b-button
        v-if="canMakeDraft"
        id="draft-button"
        size="sm"
        variant="secondary"
        :disabled="!canSubmit"
        @click="handleDraftSubmit"
      >
        <b-spinner v-if="!canSubmit"></b-spinner>
        <div :style="canSubmit ? '' : 'opacity: 50%;'">
          Save as Private Draft
        </div>
      </b-button>
      <b-button
        v-if="canPublish"
        id="submit-button"
        size="sm"
        variant="primary"
        :disabled="!canSubmit"
        @click="handleSubmit"
      >
        <b-spinner v-if="!canSubmit" small></b-spinner>
        <div data-qa="submit-node-modal" :style="canSubmit ? '' : 'opacity: 50%;'">
          Publish
        </div>
      </b-button>
      <b-form-invalid-feedback :state="canMakeDraft">
        {{ warningText }}
      </b-form-invalid-feedback>
    </template>
    <div v-if="loadDuration">
      <iframe
        v-if="node.mediaType === 'h5p'"
        ref="frame"
        class="duration-calculator"
        :src="node.typeData.mediaURL"
        @load="setH5pDuration"
      ></iframe>
      <video
        v-if="node.mediaFormat === 'mp4'"
        ref="video"
        :src="node.typeData.mediaURL"
        style="display: none;"
        @loadeddata="setVideoDuration"
      ></video>
      <youtube
        v-if="node.mediaFormat === 'youtube'"
        :video-id="node.typeData.youtubeID"
        :player-vars="{ autoplay: 0 }"
        style="display: none;"
        @ready="setYouTubeDuration"
      ></youtube>
    </div>
  </b-modal>
</template>

<script>
import { mapActions, mapGetters, mapMutations, mapState } from "vuex"
import { SlickList, SlickItem } from "vue-slicksort"
import ActivityForm from "./node-modal/content-form/ActivityForm"
import AppearanceForm from "./node-modal/AppearanceForm"
import BehaviourForm from "./node-modal/BehaviourForm"
import ConditionsForm from "./node-modal/ConditionsForm"
import ContentForm from "./node-modal/ContentForm"
import MoreInformationForm from "./node-modal/MoreInformationForm"
import PermissionsTable from "./node-modal/PermissionsTable"
import DeleteNodeButton from "./node-modal/DeleteNodeButton"
import { names } from "@/config/routes"
import Helpers from "@/utils/Helpers"
import { sizes } from "@/utils/constants"
import { getLinkMetadata } from "@/services/LinkPreviewApi"
import DragSelectModular from "@/utils/dragSelectModular"
import * as wp from "@/services/wp"

const shouldFetch = (url, selectedNode) => {
  if (!selectedNode.typeData.linkMetadata) {
    return true
  }
  const oldUrl = selectedNode.typeData.linkMetadata.url
  return oldUrl != Helpers.normalizeUrl(url)
}

export default {
  name: "node-modal",
  components: {
    AppearanceForm,
    BehaviourForm,
    ContentForm,
    ActivityForm,
    ConditionsForm,
    MoreInformationForm,
    SlickItem,
    SlickList,
    PermissionsTable,
    DeleteNodeButton,
  },
  data() {
    return {
      loading: false,
      userId: null,
      formErrors: [],
      maxDescriptionLength: 250,
      node: null,
      videoLoaded: false,
      fileUploading: false,
      loadDuration: false,
      warningText: "",
    }
  },
  computed: {
    ...mapGetters([
      "createDefaultNode",
      "getDirectChildren",
      "getParent",
      "getNode",
      "getNeighbours",
    ]),
    ...mapState(["nodes", "rootId", "settings", "visibleNodes"]),
    parent() {
      const parent = this.getNode(
        this.type === "add" ? this.nodeId : this.getParent(this.nodeId)
      )
      return parent ? parent : null
    },
    title() {
      if (this.type === "add") {
        return this.parent
          ? `Add new sub-topic to ${this.parent.title}`
          : "Add root node"
      } else if (this.type === "edit") {
        return `Edit node: ${this.node.title}`
      }
      return ""
    },
    isAuthenticated() {
      return wp.isLoggedIn()
    },
    viewAccess() {
      return this.settings.showAccess === undefined
        ? true
        : this.settings.showAccess
        ? true
        : wp.canEditTapestry()
    },
    canPublish() {
      if (this.loading) return false
      if (this.type === "add") {
        return (
          Helpers.hasPermission(this.parent, this.type) &&
          (!this.parent || this.parent.status !== "draft")
        )
      } else if (this.node.status === "draft" && this.type === "edit") {
        if (this.node.id === this.rootId) {
          return true
        }
        return this.getNeighbours(this.nodeId).some(neighbourId => {
          let neighbour = this.getNode(neighbourId)
          return (
            neighbour.status !== "draft" && Helpers.hasPermission(neighbour, "add")
          )
        })
      } else {
        return Helpers.hasPermission(this.node, this.type)
      }
    },
    authoredNode() {
      const { id } = wp.getCurrentUser()
      if (this.node.author) {
        return parseInt(this.node.author.id) === id
      }
      return true
    },
    canMakeDraft() {
      const { id } = wp.getCurrentUser()
      if (this.node.status === "publish" && this.type === "edit") {
        return false
      }
      return this.hasDraftPermission(id)
    },
    canSubmit() {
      return !this.fileUploading
    },
    nodeId() {
      const nodeId = this.$route.params.nodeId
      return nodeId || Number(nodeId)
    },
    show() {
      return this.$route.name === names.MODAL
    },
    tab() {
      return this.$route.params.tab || ""
    },
    type() {
      return this.$route.params.type || ""
    },
  },
  watch: {
    nodeId: {
      immediate: true,
      handler() {
        if (this.show) {
          if (this.isValid()) {
            this.initialize()
          }
        }
      },
    },
    show: {
      immediate: true,
      handler(show) {
        if (show) {
          if (this.isValid()) {
            DragSelectModular.removeDragSelectListener()
            this.loading = false
            this.initialize()
          }
        } else {
          DragSelectModular.addDragSelectListener()
        }
      },
    },
    type() {
      this.initialize()
    },
    tab: {
      immediate: true,
      handler() {
        if (this.show) {
          this.isValid()
        }
      },
    },
  },
  mounted() {
    this.$root.$on("node-modal::uploading", isUploading => {
      this.fileUploading = isUploading
    })
    this.node = this.createDefaultNode()
  },
  methods: {
    ...mapMutations(["updateSelectedNode", "updateRootNode"]),
    ...mapActions([
      "addNode",
      "addLink",
      "updateNode",
      "updateNodePermissions",
      "updateLockedStatus",
    ]),
    isValid() {
      const isNodeValid = this.validateNodeRoute(this.nodeId)
      if (!isNodeValid) {
        this.$router.replace({
          name: names.APP,
          params: { nodeId: this.nodeId },
        })
        return false
      }
      const isTabValid = this.validateTab(this.tab)
      if (!isTabValid) {
        this.$router.replace({
          name: names.MODAL,
          params: { nodeId: this.nodeId, type: this.type, tab: "content" },
          query: this.$route.query,
        })
      }
      return true
    },
    validateNodeRoute(nodeId) {
      if (this.type === "add") {
        if (Object.keys(this.nodes).length === 0 || this.isAuthenticated) {
          return true
        }
      }
      if (!this.nodes.hasOwnProperty(nodeId)) {
        return false
      }
      const isAllowed = Helpers.hasPermission(this.getNode(nodeId), this.type)
      const messages = {
        edit: `You don't have permission to edit this node`,
        add: `You don't have permission to add to this node`,
      }
      if (!isAllowed && this.type in messages) {
        alert(messages[this.type])
      }
      return isAllowed
    },
    initialize() {
      this.formErrors = ""
      let copy = this.createDefaultNode()
      if (this.type === "edit") {
        const node = this.getNode(this.nodeId)
        copy = Helpers.deepCopy(node)
      }
      copy.hasSubAccordion = this.hasSubAccordion(copy)
      this.node = copy
    },
    validateTab(requestedTab) {
      // Tabs that are valid for ALL node types and modal types
      const okTabs = ["content", "appearance", "more-information"]
      if (okTabs.includes(requestedTab)) {
        return true
      }

      // If requested tab is access, check if the user can access it
      if (requestedTab === "access") {
        return this.viewAccess
      }

      switch (requestedTab) {
        case "activity": {
          return this.node.mediaType === "h5p" || this.node.mediaType === "video"
        }
        case "behaviour": {
          return this.node.mediaType === "h5p" || this.node.mediaType === "video"
        }
        case "ordering": {
          return this.node.mediaType === "accordion" || this.node.hasSubAccordion
        }
      }

      return false
    },
    hasSubAccordion(node) {
      if (this.parent) {
        const children = this.getDirectChildren(node.id)
        return this.parent.mediaType === "accordion" && children.length > 0
      }
      return false
    },
    changeTab(tab) {
      // Prevent multiple clicks
      if (tab !== this.tab) {
        this.$router.push({
          name: names.MODAL,
          params: { nodeId: this.nodeId, type: this.type, tab },
          query: this.$route.query,
        })
      }
    },
    close() {
      if (this.show) {
        if (Object.keys(this.nodes).length === 0) {
          this.$router.push({ path: "/", query: this.$route.query })
        } else if (this.rootId && !this.nodeId) {
          // We just added a root node
          this.$router.push({
            name: names.APP,
            params: { nodeId: this.rootId },
            query: this.$route.query,
          })
        } else {
          this.$router.push({
            name: names.APP,
            params: { nodeId: this.nodeId },
            query: this.$route.query,
          })
        }
      }
    },
    async handleSubmit() {
      this.loading = true
      this.formErrors = this.validateNode()
      if (!this.formErrors.length) {
        this.node.status = "publish"
        this.updateNodeCoordinates()

        if (this.node.mediaType === "url-embed" && this.node.behaviour !== "embed") {
          await this.setLinkData()
        }

        if (this.shouldReloadDuration()) {
          this.loadDuration = true
        } else {
          return this.submitNode()
        }
      }
      this.loading = false
    },
    async handleDraftSubmit() {
      this.loading = true
      this.formErrors = this.validateNode()
      if (!this.formErrors.length) {
        this.node.status = "draft"
        this.updateNodeCoordinates()

        if (this.node.mediaType == "url-embed" && this.node.behaviour != "embed") {
          await this.setLinkData()
        }

        if (this.shouldReloadDuration()) {
          this.loadDuration = true
        } else {
          return this.submitNode()
        }
      }
      this.loading = false
    },
    async submitNode() {
      if (this.type === "add") {
        const id = await this.addNode(this.node)
        this.node.id = id
        if (this.parent) {
          // Add link from parent node to this node
          const newLink = {
            source: this.parent.id,
            target: id,
            value: 1,
            type: "",
          }
          await this.addLink(newLink)
        } else {
          this.updateRootNode(id)
        }
      } else {
        await this.updateNode({
          id: this.node.id,
          newNode: this.node,
        })
      }
      await this.updateLockedStatus()
      this.updateSelectedNode(this.node.id)
      this.$router.push({
          name: names.APP,
          params: { nodeId: this.node.id },
          query: this.$route.query,
        })
      this.close()
      this.loading = false
    },
    getRandomNumber(min, max) {
      return Math.random() * (max - min) + min
    },
    coinToss() {
      return Math.floor(Math.random() * 2) == 0
    },
    calculateX(yIsCalculated) {
      if (!yIsCalculated) {
        if (this.coinToss()) {
          this.node.coordinates.x = this.getRandomNumber(
            this.parent.coordinates.x +
              sizes.NODE_RADIUS_SELECTED +
              sizes.NODE_RADIUS,
            this.parent.coordinates.x + sizes.NODE_RADIUS_SELECTED * 2
          )
        } else {
          this.node.coordinates.x = this.getRandomNumber(
            this.parent.coordinates.x -
              sizes.NODE_RADIUS_SELECTED -
              sizes.NODE_RADIUS,
            this.parent.coordinates.x - sizes.NODE_RADIUS_SELECTED * 2
          )
        }
        this.calculateY(true)
      } else {
        this.node.coordinates.x = this.getRandomNumber(
          this.parent.coordinates.x - sizes.NODE_RADIUS_SELECTED * 2,
          this.parent.coordinates.x + sizes.NODE_RADIUS_SELECTED * 2
        )
      }
    },
    calculateY(xIsCalculated) {
      if (!xIsCalculated) {
        if (this.coinToss()) {
          this.node.coordinates.y = this.getRandomNumber(
            this.parent.coordinates.y +
              sizes.NODE_RADIUS_SELECTED +
              sizes.NODE_RADIUS,
            this.parent.coordinates.y + sizes.NODE_RADIUS_SELECTED * 2
          )
        } else {
          this.node.coordinates.y = this.getRandomNumber(
            this.parent.coordinates.y -
              sizes.NODE_RADIUS_SELECTED -
              sizes.NODE_RADIUS,
            this.parent.coordinates.y - sizes.NODE_RADIUS_SELECTED * 2
          )
        }
        this.calculateX(true)
      } else {
        this.node.coordinates.y = this.getRandomNumber(
          this.parent.coordinates.y - sizes.NODE_RADIUS_SELECTED * 2,
          this.parent.coordinates.y + sizes.NODE_RADIUS_SELECTED * 2
        )
      }
    },
    updateNodeCoordinates() {
      if (this.type === "add" && this.parent) {
        this.coinToss() ? this.calculateX(false) : this.calculateY(false)
      }
    },
    validateNode() {
      const errMsgs = []

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
          this.node.mediaDuration = 0
        }
      } else if (this.node.mediaType === "h5p") {
        if (this.node.typeData.mediaURL === "") {
          errMsgs.push("Please select an H5P content for this node")
        }
        if (!Helpers.onlyContainsDigits(this.node.mediaDuration)) {
          this.node.mediaDuration = 0
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

      return errMsgs
    },
    validateQuiz(quiz) {
      return quiz.every(question => {
        return Object.values(question.answers).some(
          value => value && value.length > 0
        )
      })
    },
    updateOrderingArray(arr) {
      this.node.childOrdering = arr
    },
    handleTypeChange() {
      this.node.quiz = this.node.quiz.filter(q =>
        Object.values(q.answers).reduce((acc, { value }) => acc || value == "")
      )
    },
    async setLinkData() {
      if (shouldFetch(this.node.typeData.mediaURL, this.node)) {
        const url = this.node.typeData.mediaURL
        const { data } = await getLinkMetadata(url)

        if (data) {
          this.node.typeData.linkMetadata = data
          if (
            confirm(
              "Would you like to use the link preview image as the thumbnail image?"
            )
          ) {
            this.node.imageURL = data.image
          }
          if (
            confirm(
              "Would you like to use the link preview image as the locked thumbnail image?"
            )
          ) {
            this.node.lockedImageURL = data.image
          }
        }
      }
    },
    setVideoDuration() {
      this.node.mediaDuration = parseInt(this.$refs.video.duration)
      this.loadDuration = false
      return this.submitNode()
    },
    setYouTubeDuration(evt) {
      this.node.mediaDuration = evt.target.getDuration()
      this.loadDuration = false
      return this.submitNode()
    },
    setH5pDuration() {
      const frame = this.$refs.frame
      const h5p = frame.contentWindow.H5P
      if (h5p) {
        const instance = h5p.instances[0]
        const libraryName = instance.libraryInfo.machineName
        if (libraryName === "H5P.InteractiveVideo") {
          const h5pVideo = instance.video
          const handleH5PLoad = () => {
            this.node.mediaDuration = parseInt(h5pVideo.getDuration())
            this.loadDuration = false
            return this.submitNode()
          }
          if (h5pVideo.getDuration() !== undefined) {
            handleH5PLoad()
          } else {
            h5pVideo.on("loaded", handleH5PLoad)
          }
          return
        } else {
          this.node.mediaDuration = 0
        }
      }
      this.loadDuration = false
      return this.submitNode()
    },
    shouldReloadDuration() {
      if (this.node.mediaType !== "video" && this.node.mediaType !== "h5p") {
        return false
      }
      if (this.type === "add") {
        return true
      }
      const oldNode = this.getNode(this.nodeId)
      const { youtubeID, mediaURL } = oldNode.typeData
      return this.node.mediaFormat === "youtube"
        ? this.node.typeData.youtubeID !== youtubeID
        : this.node.mediaURL !== mediaURL
    },
    hasDraftPermission(ID) {
      if (ID === 0) {
        this.warningText = "You must be authenticated to create a draft node"
        return false
      }

      if (this.type === "edit") {
        if (!this.authoredNode) {
          this.warningText = "You cannot make nodes you did not author into drafts"
          return false
        }
      }

      return true
    },
  },
}
</script>

<style lang="scss" scoped>
.duration-calculator {
  position: fixed;
  left: 101vw;
  width: 1px;
}

h6 {
  font-weight: 400;
}
</style>

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
.spinner {
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

#submit-button {
  display: flex;
  align-items: center;
  flex-direction: row-reverse;

  div {
    margin-right: 4px;
  }
}

#node-modal-container {
  * {
    outline: none;
  }

  .form-control {
    padding: 15px;
    border: none;
    background: #f1f1f1;
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
}

.modal-header-row {
  display: flex;
  justify-content: space-between;
  width: 100%;
  margin-bottom: 8px;

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

.indented-options {
  border-left: solid 2px #ccc;
  padding-left: 1em;
}

button:disabled {
  cursor: not-allowed;
}
</style>
