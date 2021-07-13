<template>
  <div>
    <div v-for="(menuGroup, groupIndex) in menuGroups" :key="groupIndex">
      <headless-multi-content
        v-if="menuGroup[0].node.id === activeMenuNode"
        :rows="menuGroup.map(row => row.node.id)"
        :value="rowId"
        @input="changeRow"
      >
        <template v-slot="{ isVisible, hasNext, next }">
          <div data-qa="page-rows">
            <div
              v-for="(row, index) in menuGroup"
              :id="`row-${row.node.id}`"
              :key="row.node.id"
              ref="rowRefs"
              class="page-row"
              :style="rowBackground"
            >
              <div class="title-row-icon">
                <i
                  v-if="disableRow(index, row.node)"
                  class="fas fa-lock fa-sm"
                  style="color:white;"
                ></i>
                <a v-else>
                  <i
                    class="fas fa-heart fa-sm"
                    :style="{
                      color: isFavourite(row.node.id) ? 'red' : 'white',
                      cursor: 'pointer',
                    }"
                    @click="toggleFavourite(row.node.id)"
                  ></i>
                </a>
              </div>
              <div v-if="disableRow(index, row.node)">
                <h1 class="title">
                  {{ row.node.title }}
                </h1>
                <locked-content :node="row.node"></locked-content>
              </div>
              <div v-else :data-qa="`row-content-${row.node.id}`">
                <div v-if="row.node.mediaType !== 'multi-content'">
                  <tapestry-media
                    :node-id="row.node.id"
                    :dimensions="dimensions"
                    context="page"
                    style="color: white; margin-bottom: 24px;"
                    @complete="updateProgress(row.node.id)"
                    @load="handleLoad($refs.rowRefs[index])"
                  />
                  <p v-if="row.children.length > 0" style="color: white;">
                    {{ row.node.typeData.subAccordionText }}
                  </p>
                  <accordion-rows
                    v-if="row.children.length > 0"
                    :dimensions="dimensions"
                    :node="row.node"
                    :rowId="subRowId"
                    context="page"
                    :level="level + 1"
                    @changeRow="changeRow"
                    @load="handleLoad"
                    @updateProgress="updateProgress"
                  ></accordion-rows>
                </div>
                <multi-content-media
                  v-else-if="row.children.length > 0"
                  :node="getNode(row.node.id)"
                  :row-id="subRowId"
                  context="page"
                  :level="level + 1"
                  @close="handleAutoClose"
                  @complete="updateProgress"
                />
              </div>
              <button
                v-if="row.node.completed && isVisible(row)"
                class="mt-2"
                @click="hasNext ? next() : (showCompletion = true)"
              >
                {{ node.typeData.finishButtonText }}
              </button>
            </div>
          </div>
        </template>
      </headless-multi-content>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions, mapMutations } from "vuex"
import TapestryMedia from "../TapestryMedia"
import HeadlessMultiContent from "./HeadlessMultiContent"
import AccordionRows from "./AccordionRows"
import LockedContent from "./common/LockedContent"

export default {
  name: "page-rows",
  components: {
    TapestryMedia,
    HeadlessMultiContent,
    MultiContentMedia: () => import("../MultiContentMedia"),
    AccordionRows,
    LockedContent,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
    rowId: {
      type: Number,
      required: true,
    },
    subRowId: {
      type: Number,
      required: false,
      default: 0,
    },
    dimensions: {
      type: Object,
      required: true,
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
    activeMenuNode: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  data() {
    return {
      showCompletion: false,
    }
  },
  computed: {
    ...mapGetters(["getDirectChildren", "getNode", "isFavourite", "isMultiContent"]),
    ...mapState(["favourites"]),
    rows() {
      return this.node.childOrdering.map(id => {
        const node = this.getNode(id)
        const children = this.isMultiContent(node.id)
          ? node.childOrdering.map(this.getNode)
          : this.getDirectChildren(id).map(this.getNode)
        return { node, children }
      })
    },
    menuGroups() {
      const menu = []
      const mainMenu = []
      this.rows.forEach(row => {
        if (row.node.typeData.isSecondaryNode) {
          let subMenu = []
          subMenu.push(row)
          menu.push(subMenu)
        } else {
          mainMenu.push(row)
        }
      })
      menu.unshift(mainMenu)
      return menu
    },
    lockRows() {
      return this.node.typeData.lockRows
    },
    disabledFrom() {
      return this.rows.findIndex(row => !row.node.completed)
    },
    isMultiContentContext() {
      return (
        this.context === "multi-content" ||
        this.context === "page" ||
        this.context === "accordion"
      )
    },
    rowBackground() {
      if (this.isMultiContentContext) {
        let rgb = 40
        let colorOffset = this.level * 10
        rgb = colorOffset > rgb ? 0 : rgb - colorOffset
        return {
          background: `rgb(${rgb}, ${rgb}, ${rgb})`,
        }
      } else {
        return null
      }
    },
  },
  mounted() {
    this.$root.$emit("observe-rows", this.$refs.rowRefs)
  },
  methods: {
    ...mapMutations(["updateNode"]),
    ...mapActions(["completeNode", "toggleFavourite"]),
    handleLoad(el) {
      this.$emit("load", el)
    },
    disableRow(index, node) {
      return (
        (this.lockRows && this.disabledFrom >= 0 && index > this.disabledFrom) ||
        !node.unlocked
      )
    },
    updateProgress(rowId) {
      this.$emit("updateProgress", rowId)
    },
    changeRow(rowInfo) {
      this.$emit("changeRow", { context: this.context, ...rowInfo })
    },
    handleAutoClose() {
      this.$emit("close")
    },
    areAllPopup(nodes) {
      return nodes.every(node => node.popup !== null)
    },
  },
}
</script>

<style lang="scss" scoped>
button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}

.title-row-icon {
  flex: 1;
  text-align: right;
}

.title {
  background: none;
  width: 100%;
  text-align: left;
  color: #fff;
  font-size: 1.75rem;
  font-weight: 500;

  :before {
    display: none;
  }
}

.page-row {
  background: #262626;
  border-radius: 4px;
  padding: 8px 16px;
  margin-bottom: 8px;

  &:last-child {
    margin-bottom: 0;
  }
}
</style>
