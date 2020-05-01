<template>
  <div id="tapestry">
    <div
      v-if="!tapestryLoaded"
      class="d-flex justify-content-center mb-3"
      style="padding: 30vh 0;"
    >
      <label>Loading Tapestry</label>
      <b-spinner
        type="grow"
        variant="secondary"
        small
        style="margin: 5px 5px 5px 20px;"
      ></b-spinner>
      <b-spinner
        type="grow"
        variant="primary"
        small
        style="margin: 5px;"
      ></b-spinner>
      <b-spinner type="grow" variant="danger" small style="margin: 5px;"></b-spinner>
    </div>
    <settings-modal :wp-can-edit-tapestry="wpCanEditTapestry" />
    <root-node-button v-if="showRootNodeButton" @add-root-node="addRootNode" />
    <div v-if="showEmpty" style="margin-top: 40vh;">
      The requested tapestry is empty.
    </div>
    <node-modal
      :node="populatedNode"
      :parent="parentNode"
      :modal-type="modalType"
      :root-node-title="selectedNode.title"
      :permissions-order="permissionsOrder"
      @close-modal="closeModal"
      @add-edit-node="addEditNode"
      @delete-node="deleteNode"
    />
  </div>
</template>

<script>
import { mapGetters, mapMutations, mapActions } from "vuex"
import NodeModal from "./NodeModal"
import SettingsModal from "./SettingsModal"
import RootNodeButton from "./RootNodeButton"
import TapestryApi from "../services/TapestryAPI"
import { tydeTypes } from "@/utils/constants"
import { getLinkMetadata } from "../services/LinkPreviewApi"
import Helpers from "../utils/Helpers"

export default {
  name: "tapestry",
  components: {
    NodeModal,
    RootNodeButton,
    SettingsModal,
  },
  data() {
    return {
      loadedH5pId: 0,
      TapestryAPI: new TapestryApi(wpPostId),
      tapestryLoaded: false,
      modalType: "",
      parentNode: null,
      populatedNode: {
        title: "",
        description: "",
        mediaType: "",
        typeData: {
          mediaURL: "",
          textContent: "",
        },
        mediaDuration: "",
        imageURL: "",
        lockedImageURL: "",
        showInBackpack: true,
        permissions: {
          public: ["read"],
          authenticated: ["read"],
        },
        quiz: [],
        skippable: true,
        tydeType: tydeTypes.REGULAR,
      },
      favourites: [],
    }
  },
  computed: {
    ...mapGetters(["selectedNode", "tapestry", "getNode", "getParent"]),
    showRootNodeButton: function() {
      return (
        this.tapestryLoaded &&
        !this.tapestry.rootId &&
        thisTapestryTool.canCurrentUserEdit()
      )
    },
    showEmpty: function() {
      return (
        this.tapestryLoaded &&
        !this.tapestry.rootId &&
        !thisTapestryTool.canCurrentUserEdit()
      )
    },
    xORfx: function() {
      return this.tapestry.settings.autoLayout ? "x" : "fx"
    },
    yORfy: function() {
      return this.tapestry.settings.autoLayout ? "y" : "fy"
    },
    permissionsOrder: function() {
      switch (this.modalType) {
        case "edit-node":
          return this.selectedNode.permissionsOrder
        default:
          return [
            "public",
            "authenticated",
            ...Object.keys(wpData.roles).filter(
              role => role !== "administrator" && role !== "author"
            ),
          ]
      }
    },
    wpCanEditTapestry: function() {
      return wpApiSettings && wpApiSettings.wpCanEditTapestry === "1"
    },
  },
  watch: {
    selectedNode() {
      this.parentNode = this.getParent(this.selectedNode)
    },
  },
  async created() {
    const tapestryApi = new TapestryApi(wpPostId)
    this.favourites = await tapestryApi.getUserFavourites()
  },
  mounted() {
    window.addEventListener("change-selected-node", this.changeSelectedNode)
    window.addEventListener("add-new-node", this.addNewNode)
    window.addEventListener("edit-node", this.editNode)
    window.addEventListener("tapestry-updated", this.tapestryUpdated)
  },
  methods: {
    ...mapMutations([
      "init",
      "setDataset",
      "updateSelectedNode",
      "updateRootNode",
      "updateNodeCoordinates",
      "updateTydeProgress",
    ]),
    ...mapActions(["addNode", "addLink", "updateNode", "updateNodePermissions"]),
    tapestryUpdated(event) {
      if (!this.tapestryLoaded) {
        event.detail.dataset["favourites"] = this.favourites
        this.init(event.detail.dataset)
        this.tapestryLoaded = true
      } else {
        this.setDataset(event.detail.dataset)
      }
      const stages = this.tapestry.nodes.filter(n => n.tydeType === tydeTypes.STAGE)
      stages.map(n =>
        this.updateTydeProgress({ parentId: n.id, isParentModule: false })
      )
    },
    getEmptyNode() {
      return {
        title: "",
        behaviour: "embed",
        mediaType: "",
        typeData: {
          mediaURL: "",
          textContent: "",
          subAccordionText: "More content:",
        },
        mediaDuration: "",
        imageURL: "",
        lockedImageURL: "",
        hideTitle: false,
        hideProgress: false,
        hideMedia: false,
        skippable: true,
        fullscreen: false,
        showInBackpack: true,
        permissions: {
          public: ["read"],
          authenticated: ["read"],
        },
        description: "",
        quiz: [],
        tydeType: tydeTypes.REGULAR,
        childOrdering: [],
      }
    },
    addRootNode() {
      this.modalType = "add-root-node"
      this.parentNode = null
      this.populatedNode = this.getEmptyNode()
      this.$bvModal.show("node-modal-container")
    },
    addNewNode() {
      this.modalType = "add-new-node"
      this.parentNode = this.selectedNode
      this.populatedNode = this.getEmptyNode()
      this.$bvModal.show("node-modal-container")
    },
    editNode() {
      this.modalType = "edit-node"
      this.parentNode = this.getParent(this.selectedNode)
      this.populatedNode = this.selectedNode
      this.$bvModal.show("node-modal-container")
    },
    deleteNode() {
      thisTapestryTool.deleteNodeFromTapestry()
      this.closeModal()
    },
    closeModal() {
      this.modalType = ""
      this.parent = null
      this.$bvModal.hide("node-modal-container")
    },
    changeSelectedNode(event) {
      this.updateSelectedNode(event.detail)
    },
    async addEditNode(formData, isEdit) {
      const NORMAL_RADIUS = 140
      const ROOT_RADIUS_DIFF = 70

      var isRoot = this.$store.state.nodes.length === 0

      // Add the node data first
      var newNodeEntry = {
        type: "tapestry_node",
        description: "",
        conditions: [],
        behaviour: "embed",
        status: "publish",
        nodeType: "",
        title: "",
        imageURL: "",
        lockedImageURL: "",
        mediaType: "video",
        mediaFormat: "",
        mediaDuration: 0,
        typeId: 1,
        group: 1,
        typeData: {
          linkMetadata: null,
          progress: [
            { group: "viewed", value: 0 },
            { group: "unviewed", value: 1 },
          ],
          mediaURL: "",
          mediaWidth: 960, //TODO: This needs to be flexible with H5P
          mediaHeight: 600,
          planetViewNotEarnedIconUrl: "",
          planetViewEarnedIconUrl: "",
          spaceshipPartNotEarnedIconUrl: "",
          spaceshipPartEarnedIconUrl: "",
          spaceshipPartHoverIconUrl: "",
          spaceshipPartX: 0,
          spaceshipPartY: 0,
          spaceshipPartWidth: 0,
          spaceshipPartHeight: 0,
          subAccordionText: "More content:",
        },
        hideTitle: false,
        hideProgress: false,
        hideMedia: false,
        skippable: true,
        fullscreen: false,
        tydeType: tydeTypes.REGULAR,
        showInBackpack: true,
        coordinates: {
          x: 3000,
          y: 3000,
        },
        childOrdering: [],
      }

      if (isEdit) {
        // If just editing, set the node coordinates to its current location
        newNodeEntry.coordinates.x = this.selectedNode.x
        newNodeEntry.coordinates.y = this.selectedNode.y

        newNodeEntry.typeData.linkMetadata = this.selectedNode.typeData.linkMetadata
      } else if (!isRoot) {
        // If adding a new node, add it to the right of the existing node
        newNodeEntry.coordinates.x =
          this.selectedNode.x + (NORMAL_RADIUS + ROOT_RADIUS_DIFF) * 2 + 50
        newNodeEntry.coordinates.y = this.selectedNode.y
      }

      newNodeEntry.typeData = {
        ...newNodeEntry.typeData,
        ...this.populatedNode.typeData,
      }

      var appearsAt = 0
      for (var i = 0; i < formData.length; i++) {
        var fieldName = formData[i].name
        var fieldValue = formData[i].value

        switch (fieldName) {
          case "title":
            newNodeEntry[fieldName] = fieldValue
            break
          case "imageURL":
            newNodeEntry[fieldName] = fieldValue || ""
            break
          case "lockedImageURL":
            newNodeEntry[fieldName] = fieldValue || ""
            break
          case "behaviour":
            newNodeEntry[fieldName] = fieldValue
            break
          case "mediaType":
            if (fieldValue === "text") {
              newNodeEntry["mediaType"] = "text"
            } else if (fieldValue === "video") {
              newNodeEntry["mediaType"] = "video"
              newNodeEntry["mediaFormat"] = "mp4"
            } else if (fieldValue === "h5p") {
              newNodeEntry["mediaType"] = "video"
              newNodeEntry["mediaFormat"] = "h5p"
            } else if (fieldValue === "url-embed") {
              newNodeEntry["mediaType"] = "url-embed"
              newNodeEntry["mediaFormat"] = "embed"
            } else if (fieldValue === "gravity-form") {
              newNodeEntry.mediaType = "gravity-form"
              newNodeEntry.mediaFormat = "embed"
            } else {
              newNodeEntry.mediaType = fieldValue
              newNodeEntry.mediaFormat = ""
            }
            break
          case "textContent":
            if (fieldValue) {
              newNodeEntry.typeData.textContent = fieldValue
            }
            break
          case "mediaURL":
            if (fieldValue) {
              newNodeEntry.typeData.mediaURL = fieldValue
            }
            break
          case "mediaDuration":
            if (fieldValue) {
              newNodeEntry.mediaDuration = parseInt(fieldValue)
            }
            break
          case "hideTitle":
            newNodeEntry.hideTitle = fieldValue
            break
          case "hideProgress":
            newNodeEntry.hideProgress = fieldValue
            break
          case "hideMedia":
            newNodeEntry.hideMedia = fieldValue
            break
          case "skippable":
            newNodeEntry.skippable = fieldValue
            break
          case "fullscreen":
            newNodeEntry.fullscreen = fieldValue
            break
          case "description":
            newNodeEntry.description = fieldValue
            break
          case "permissions":
            newNodeEntry.permissions = fieldValue
            break
          case "quiz":
            newNodeEntry.quiz = fieldValue
            break
          case "tydeType":
            newNodeEntry.tydeType = fieldValue
            break
          case "showInBackpack":
            newNodeEntry.showInBackpack = fieldValue
            break
          case "planetViewNotEarnedIconUrl":
            newNodeEntry.typeData.planetViewNotEarnedIconUrl = fieldValue
            break
          case "planetViewEarnedIconUrl":
            newNodeEntry.typeData.planetViewEarnedIconUrl = fieldValue
            break
          case "spaceshipPartNotEarnedIconUrl":
            newNodeEntry.typeData.spaceshipPartNotEarnedIconUrl = fieldValue
            break
          case "spaceshipPartEarnedIconUrl":
            newNodeEntry.typeData.spaceshipPartEarnedIconUrl = fieldValue
            break
          case "spaceshipPartHoverIconUrl":
            newNodeEntry.typeData.spaceshipPartHoverIconUrl = fieldValue
            break
          case "spaceshipPartX":
            newNodeEntry.typeData.spaceshipPartX = fieldValue
            break
          case "spaceshipPartY":
            newNodeEntry.typeData.spaceshipPartY = fieldValue
            break
          case "spaceshipPartWidth":
            newNodeEntry.typeData.spaceshipPartWidth = fieldValue
            break
          case "spaceshipPartHeight":
            newNodeEntry.typeData.spaceshipPartHeight = fieldValue
            break
          case "subAccordionText":
            newNodeEntry.typeData.subAccordionText = fieldValue
            break
          case "childOrdering":
            newNodeEntry.childOrdering = fieldValue
            break
          default:
            newNodeEntry[fieldName] = fieldValue
            break
        }
      }

      if (
        newNodeEntry.mediaFormat === "embed" &&
        newNodeEntry.behaviour !== "embed"
      ) {
        if (
          !isEdit ||
          shouldFetch(newNodeEntry.typeData.mediaURL, this.selectedNode)
        ) {
          const url = newNodeEntry.typeData.mediaURL
          const { data } = await getLinkMetadata(url)
          newNodeEntry.typeData.linkMetadata = data

          let shouldChange = true
          if (newNodeEntry.imageURL) {
            shouldChange = confirm("Change thumbnail to new image?")
          }

          if (shouldChange) {
            newNodeEntry.imageURL = data.image
          }

          if (newNodeEntry.lockedImageURL) {
            shouldChange = confirm("Change locked thumbnail to new image?")
          }

          if (shouldChange) {
            newNodeEntry.lockedImageURL = data.image
          }
        }
      }

      let id
      if (!isEdit) {
        // New node
        id = await this.addNode({
          newNode: newNodeEntry,
          parentId: this.parentNode && this.parentNode.id,
        })
        newNodeEntry.id = id
        if (!isRoot) {
          // Add link from parent node to this node
          const newLink = {
            source: this.selectedNode.id,
            target: newNodeEntry.id,
            value: 1,
            type: "",
            appearsAt: appearsAt,
          }
          await this.addLink(newLink)
          this.selectedNode.childOrdering.push(id)
        } else {
          this.updateRootNode(newNodeEntry.id)
          this.updateSelectedNode(newNodeEntry.id)
        }
      } else {
        // Editing existing node
        id = await this.updateNode({
          id: this.selectedNode.id,
          newNode: newNodeEntry,
        })
      }

      // Update permissions
      this.updateNodePermissions({
        id,
        permissions: newNodeEntry.permissions,
      })

      // Update coordinates in dataset
      this.updateNodeCoordinates({
        id,
        coordinates: {
          [this.xORfx]: newNodeEntry.coordinates.x,
          [this.yORfy]: newNodeEntry.coordinates.y,
        },
      })

      thisTapestryTool.setDataset(this.tapestry)
      thisTapestryTool.setOriginalDataset(this.tapestry)
      thisTapestryTool.initialize(true)

      this.closeModal()
    },
    /* handleSettingsUpdate(settings) {
      this.$store.commit("updateSettings", settings)
      thisTapestryTool.setDataset(this.tapestry)
      thisTapestryTool.reinitialize()
    }, */
  },
}

const shouldFetch = (url, selectedNode) => {
  if (!selectedNode.typeData.linkMetadata) {
    return true
  }
  const oldUrl = selectedNode.typeData.linkMetadata.url
  return !oldUrl.startsWith(Helpers.normalizeUrl(url))
}
</script>

<style lang="scss" scoped></style>
