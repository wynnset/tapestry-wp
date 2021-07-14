<template>
  <div
    ref="container"
    class="media-container"
    :style="navBarStyle"
    data-qa="multi-content"
  >
    <header>
      <h1
        v-if="showTitle"
        :class="{
          title: true,
          'nested-media-title': isMultiContentContext,
        }"
      >
        {{ node.title }}
      </h1>
    </header>
    <accordion-rows
      v-if="node.presentationStyle === 'accordion'"
      :dimensions="dimensions"
      :node="node"
      :rowId="rowId"
      :subRowId="subRowId"
      :context="context"
      :level="level"
      @load="handleLoad"
      @changeRow="changeRow"
      @updateProgress="updateProgress"
    ></accordion-rows>
    <page-rows
      v-else-if="node.presentationStyle === 'page'"
      :dimensions="dimensions"
      :node="node"
      :rowId="rowId"
      :subRowId="subRowId"
      :context="context"
      :level="level"
      @load="handleLoad"
      @changeRow="changeRow"
      @updateProgress="updateProgress"
    ></page-rows>
    <tapestry-modal
      v-if="showCompletion"
      :node-id="node.id"
      :allow-close="false"
      @close="handleCancel"
    >
      <h1>{{ node.typeData.confirmationTitleText }}</h1>
      <p>{{ node.typeData.confirmationBodyText }}</p>
      <div class="button-container">
        <button class="button-completion" @click="handleClose">
          <i class="far fa-arrow-alt-circle-right fa-4x"></i>
          <p>{{ node.typeData.continueButtonText }}</p>
        </button>
        <button class="button-completion" @click="handleCancel">
          <i class="far fa-times-circle fa-4x"></i>
          <p>{{ node.typeData.cancelLinkText }}</p>
        </button>
      </div>
    </tapestry-modal>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions, mapMutations } from "vuex"
import client from "@/services/TapestryAPI"
import TapestryModal from "../../TapestryModal"
import AccordionRows from "./AccordionRows"
import PageRows from "./PageRows"
import { names } from "@/config/routes"

export default {
  name: "multi-content-media",
  components: {
    TapestryModal,
    AccordionRows,
    PageRows,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
    rowId: {
      type: Number,
      required: false,
      default: 0,
    },
    subRowId: {
      type: Number,
      required: false,
      default: 0,
    },
    context: {
      type: String,
      required: false,
      default: "",
    },
    level: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  data() {
    return {
      activeIndex: -1,
      showCompletion: false,
      isMounted: false,
      navBarStyle: {},
    }
  },
  computed: {
    ...mapGetters([
      "getDirectChildren",
      "getNode",
      "getParent",
      "isFavourite",
      "isMultiContent",
    ]),
    ...mapState(["favourites", "rootId"]),
    rows() {
      return this.node.childOrdering.map(id => {
        const node = this.getNode(id)
        const children = this.isMultiContent(node.id)
          ? node.childOrdering.map(this.getNode)
          : this.getDirectChildren(id).map(this.getNode)
        return { node, children }
      })
    },
    dimensions() {
      if (!this.isMounted) {
        return {
          width: 0,
          height: 0,
        }
      }
      const box = this.$refs.container
      const rect = box.getBoundingClientRect()
      return { width: rect.width, height: rect.height }
    },
    lockRows() {
      return this.node.typeData.lockRows
    },
    disabledFrom() {
      return this.rows.findIndex(row => !row.node.completed)
    },
    showTitle() {
      return (
        this.level == 0 ||
        (this.context !== "accordion" && this.node.typeData.showTitle !== false)
      )
    },
    isMultiContentContext() {
      return this.isNestedMultiContent(this.context)
    },
  },
  mounted() {
    this.isMounted = true
    this.activeIndex = this.node.presentationStyle === "page" ? -1 : 0
  },
  methods: {
    ...mapMutations(["updateNode"]),
    ...mapActions(["completeNode", "toggleFavourite"]),
    handleLoad(el) {
      this.$nextTick(() => {
        if (this.activeIndex >= 0) {
          client.recordAnalyticsEvent(
            "app",
            "scroll",
            "multi-content",
            this.node.id,
            {
              to: el.offsetTop - 12,
            }
          )
          this.$refs.container.scrollTop = el.offsetTop - 12
        }
      })
    },
    handleClose(evt) {
      client.recordAnalyticsEvent("user", "close", "multi-content", this.node.id, {
        x: evt.clientX,
        y: evt.clientY,
      })
      this.$emit("close")
    },
    handleCancel(evt) {
      client.recordAnalyticsEvent(
        "user",
        "close",
        "multi-content-completion-screen",
        this.node.id,
        {
          x: evt.clientX,
          y: evt.clientY,
        }
      )
      this.showCompletion = false
    },
    disableRow(index) {
      return this.lockRows && this.disabledFrom >= 0 && index > this.disabledFrom
    },
    updateProgress(rowId) {
      this.completeNode(rowId).then(() => {
        if (this.rows.every(row => row.node.completed)) {
          this.$emit("complete", this.node.id)
        }
      })
    },
    changeRow(rowInfo) {
      const { rowId, context } = rowInfo
      if (this.isNestedMultiContent(context)) {
        if (rowId) {
          this.$router.push({
            name: names.NESTEDMULTICONTENT,
            params: {
              nodeId: this.$route.params.nodeId,
              ...this.getRouteToParent(
                rowId,
                this.$route.params.rowId || this.node.id
              ),
            },
            query: this.$route.query,
          })
        } else {
          if (this.$route.params.subRowId) {
            let updatedSubRowIds = this.$route.params.subRowId.split(",")
            updatedSubRowIds.pop()
            this.$router.push({
              name: names.NESTEDMULTICONTENT,
              params: {
                nodeId: this.$route.params.nodeId,
                rowId: this.$route.params.rowId,
                subRowId: updatedSubRowIds.join(","),
              },
              query: this.$route.query,
            })
          }
        }
      } else {
        if (rowId) {
          this.$router.push({
            name: names.MULTICONTENT,
            params: { nodeId: this.node.id, rowId },
            query: this.$route.query,
          })
        } else {
          this.$router.push({
            name: names.LIGHTBOX,
            params: { nodeId: this.node.id },
            query: this.$route.query,
          })
        }
      }
    },
    isNestedMultiContent(context) {
      return (
        context === "multi-content" || context === "page" || context === "accordion"
      )
    },
    getRouteToParent(childId, parentId) {
      let path = [childId]
      let subRowIds = []

      if (this.$route.params.subRowId) {
        const subRowIdString = this.$route.params.subRowId.toString()
        subRowIds = subRowIdString.split(",")
      }

      while (childId !== this.rootId) {
        const pid = this.getParent(childId)

        // Handling shared parent in subRowIds
        if (subRowIds.includes(pid.toString())) {
          const sharedPath = subRowIds.slice(
            0,
            subRowIds.indexOf(pid.toString()) + 1
          )
          return {
            rowId: parentId,
            subRowId: sharedPath.concat(path).join(","),
          }
        }
        if (pid == this.$route.params.nodeId) {
          const rowId = path[0]
          path.shift()
          return {
            rowId: rowId,
            subRowId: path.join(","),
          }
        }
        path.unshift(pid)
        childId = pid
      }
      return {
        rowId: parentId,
        subRowId: path.join(","),
      }
    },
  },
}
</script>

<style lang="scss" scoped>
button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}

.title {
  color: rgb(51, 51, 51);
  margin-bottom: 1em;
}

.tyde-mode .title {
  text-align: left;
}

.media-container {
  height: 100%;
  width: 100%;
  max-width: 150vh;
  margin: 0 auto;
  overflow: auto;
  scrollbar-color: auto black;
  scrollbar-width: none;

  ::-webkit-scrollbar-track {
    background-color: black;
  }
}

.nested-media-title {
  text-align: left;
  font-size: 1.75rem;
  font-weight: 500;
  margin-bottom: 0.9em;

  :before {
    display: none;
  }
}

.button-completion {
  background: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: inherit;
  margin-right: 2em;

  &:last-child {
    margin-right: 0;
  }

  &:hover {
    color: #11a6d8;
  }

  p {
    margin: 1em auto 0;
    padding: 0;
    font-weight: 600;
  }
}

.button-container {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
