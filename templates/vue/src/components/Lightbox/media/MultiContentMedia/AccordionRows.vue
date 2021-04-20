<template>
  <headless-multi-content
    :rows="rows.map(row => row.node.id)"
    :value="rowId"
    @input="changeRow"
  >
    <template v-slot="{ isVisible, hasNext, next, toggle }">
      <div data-qa="accordion-rows">
        <div
          v-for="(row, index) in rows"
          :key="row.node.id"
          ref="rowRefs"
          class="accordion-row"
          :style="rowBackground"
        >
          <div class="button-row">
            <button
              class="button-row-trigger"
              :disabled="disableRow(index, row.node)"
              @click="toggle(row.node.id)"
            >
              <i
                v-if="!disableRow(index, row.node)"
                :class="isVisible(row.node.id) ? 'fas fa-minus' : 'fas fa-plus'"
              ></i>
              <i
                v-else
                class="fas fa-lock fa-sm title-row-icon"
                style="color:white;"
              ></i>
              {{ row.node.title }}
              <locked-content
                v-if="disableRow(index, row.node)"
                :node="row.node"
              ></locked-content>
            </button>
            <a
              v-if="!disableRow(index, row.node)"
              @click="toggleFavourite(row.node.id)"
            >
              <i
                v-if="isFavourite(row.node.id)"
                class="fas fa-heart fa-sm"
                style="color:red;"
              ></i>
              <i v-else class="fas fa-heart fa-sm" style="color:white;"></i>
            </a>
          </div>
          <div v-if="isVisible(row.node.id)" :data-qa="`row-content-${row.node.id}`">
            <div v-if="row.node.mediaType !== 'multi-content'">
              <tapestry-media
                :node-id="row.node.id"
                :dimensions="dimensions"
                context="multi-content"
                :autoplay="false"
                style="color: white; margin-bottom: 24px;"
                @complete="updateProgress(row.node.id)"
                @close="toggle(row.node.id)"
                @load="handleLoad($refs.rowRefs[index])"
              />
              <p v-if="row.children.length > 0" style="color: white;">
                {{ row.node.typeData.subAccordionText }}
              </p>
              <sub-accordion
                v-if="row.children.length > 0"
                :dimensions="dimensions"
                :rows="row.children"
                :row-id="subRowId"
                @load="handleLoad"
                @input="changeRow"
              />
            </div>
            <multi-content-media
              v-else-if="row.children.length > 0"
              :node="getNode(row.node.id)"
              :row-id="subRowId"
              context="accordion"
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
</template>

<script>
import { mapState, mapGetters, mapActions, mapMutations } from "vuex"
import TapestryMedia from "../TapestryMedia"
import HeadlessMultiContent from "./HeadlessMultiContent"
import SubAccordion from "./SubAccordion"
import LockedContent from "./common/LockedContent"

export default {
  name: "accordion-rows",
  components: {
    TapestryMedia,
    HeadlessMultiContent,
    MultiContentMedia: () => import("../MultiContentMedia"),
    SubAccordion,
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
  },
  data() {
    return {
      showCompletion: false,
    }
  },
  mounted() {
    this.$root.$emit("observe-rows", this.$refs.rowRefs)
  },
  computed: {
    ...mapGetters([
      "getDirectChildren",
      "getNode",
      "getParent",
      "isFavourite",
      "isMultiContent",
    ]),
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
  methods: {
    ...mapMutations(["updateNode"]),
    ...mapActions(["completeNode", "updateNodeProgress", "toggleFavourite"]),
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
    changeRow(rowId) {
      this.$emit("changeRow", rowId)
    },
    handleAutoClose() {
      this.$emit("close")
    },
  },
}
</script>

<style lang="scss" scoped>
button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}

.button-row {
  display: flex;
  align-items: center;
  margin: 0;
  width: 100%;
  border-radius: 4px;

  i {
    margin-right: 8px;
  }

  a {
    cursor: pointer;
  }
}

.button-row-trigger {
  background: none;
  width: 100%;
  text-align: left;
}

.accordion-row {
  background: #262626;
  border-radius: 4px;
  padding: 8px 16px;
  margin-bottom: 8px;

  &:last-child {
    margin-bottom: 0;
  }
}
</style>
