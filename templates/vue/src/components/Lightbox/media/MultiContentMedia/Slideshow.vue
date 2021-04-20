<template>
  <headless-multi-content
    :rows="rows.map(row => row.node.id)"
    :value="rowId"
    presentationStyle="slideshow"
    @input="changeRow"
  >
    <template>
      <div>
        <div class="slideshow-slide" data-qa="slideshow-slide">
          <div class="title-row">
            <a class="title-row-icon">
              <i
                v-if="isFavourite(slide.node.id)"
                class="fas fa-heart fa-sm"
                style="color:red; cursor:pointer;"
                @click="toggleFavourite(slide.node.id)"
              ></i>
              <i
                v-else
                class="fas fa-heart fa-sm"
                style="color:white; cursor:pointer;"
                @click="toggleFavourite(slide.node.id)"
              ></i>
            </a>
          </div>
          <div :data-qa="`row-content-${slide.node.id}`">
            <div v-if="slide.node.mediaType !== 'multi-content'">
              <tapestry-media
                :node-id="slide.node.id"
                :dimensions="dimensions"
                context="slideshow"
                :autoplay="false"
                style="color: white; margin-bottom: 24px;"
                @complete="updateProgress(slide.node.id)"
              />
              <p v-if="slide.children.length > 0" style="color: white;">
                {{ slide.node.typeData.subAccordionText }}
              </p>
              <sub-accordion
                v-if="slide.children.length > 0"
                :dimensions="dimensions"
                :rows="slide.children"
                :row-id="subRowId"
                @load="handleLoad"
                @input="changeRow"
              />
            </div>
            <multi-content-media
              v-else-if="slide.children.length > 0"
              :node="getNode(slide.node.id)"
              :row-id="subRowId"
              context="page"
              :level="level + 1"
              @close="handleAutoClose"
              @complete="updateProgress"
            />
          </div>
        </div>
        <footer class="slideshow-footer">
          <p class="slideshow-step" data-qa="slideshow-step">
            {{ currentSlideText }}
          </p>
          <button
            class="button-nav"
            data-qa="slideshow-prev-button"
            :disabled="!hasPrev"
            @click="prev"
          >
            <i class="fas fa-arrow-left"></i>
          </button>
          <button
            class="button-nav"
            data-qa="slideshow-next-button"
            :disabled="!hasNext || disableNext"
            @click="next"
          >
            <i class="fas fa-arrow-right"></i>
          </button>
        </footer>
      </div>
    </template>
  </headless-multi-content>
</template>

<script>
import { mapState, mapGetters, mapActions, mapMutations } from "vuex"
import TapestryMedia from "../TapestryMedia"
import HeadlessMultiContent from "./HeadlessMultiContent"
import SubAccordion from "./SubAccordion"

export default {
  name: "slideshow",
  components: {
    TapestryMedia,
    HeadlessMultiContent,
    MultiContentMedia: () => import("../MultiContentMedia"),
    SubAccordion,
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
      index: 0,
      disableNext: this.lockRows && !this.completed,
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
    lockRows() {
      return this.node.typeData.lockRows
    },
    completed() {
      return this.slide.node.completed
    },
    currentSlideText() {
      return `${this.index + 1}/${this.rows.length}`
    },
    slide() {
      return this.rows[this.index]
    },
    hasNext() {
      return this.index < this.rows.length - 1
    },
    hasPrev() {
      return this.index > 0
    },
  },
  watch: {
    completed: {
      immediate: true,
      handler(isCompleted) {
        this.disableNext = this.lockRows && !isCompleted
      },
    },
  },
  methods: {
    ...mapMutations(["updateNode"]),
    ...mapActions(["completeNode", "updateNodeProgress", "toggleFavourite"]),
    handleLoad(el) {
      this.$emit("load", el)
    },
    updateProgress(rowId) {
      this.$emit("updateProgress", rowId)
    },
    changeRow(rowId) {
      this.$emit("changeRow", rowId)
    },
    next() {
      if (this.hasNext && !this.disableNext) {
        this.changeRow(this.rows[++this.index].node.id)
      }
    },
    prev() {
      if (this.hasPrev) {
        this.changeRow(this.rows[--this.index].node.id)
      }
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

.title-row {
  display: flex;
  align-items: center;
  margin: 0;
  width: 100%;
  border-radius: 4px;
}

.title-row-icon {
  flex: 1;
  text-align: right;
}

.slide-row {
  background: #262626;
  border-radius: 4px;
  padding: 8px 16px;
  margin-bottom: 8px;

  &:last-child {
    margin-bottom: 0;
  }
}

.slideshow-footer {
  margin-top: 1em;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.button-nav {
  border-radius: 50%;
  height: 56px;
  width: 56px;
  background: #262626;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  color: white;
  margin: 0;
  margin-right: 12px;
  opacity: 1;
  transition: all 0.1s ease-out;

  &:hover {
    background: #11a6d8;
  }

  &:disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
  }

  &:last-child {
    margin-right: 0;
  }
}

.slideshow-step {
  margin: 0;
  padding: 0;
  font-weight: bold;
  font-size: 40px;
  color: white;
  margin-right: 32px;
}

.sub-multicontent-title {
  text-align: left;
  font-size: 1.75rem;
  font-weight: 500;
  color: white;

  :before {
    display: none;
  }
}
</style>
