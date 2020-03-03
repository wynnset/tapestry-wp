<template>
  <div ref="container" class="media-container" @scroll="handleScroll">
    <scrollbar
      :scroll-height="scrollHeight"
      :scroll-top="scrollTop"
      :client-height="clientHeight"
      @scrollchange="handleScrollDrag"
    />
    <tapestry-modal
      v-if="showCompletion"
      :allow-close="false"
      @close="showCompletion = false"
    >
      <h1>{{ node.typeData.confirmationTitleText }}</h1>
      <p>{{ node.typeData.confirmationBodyText }}</p>
      <div class="button-container">
        <button class="button-completion" @click="$emit('close')">
          <i class="far fa-arrow-alt-circle-right fa-4x"></i>
          <p>{{ node.typeData.continueButtonText }}</p>
        </button>
        <button class="button-completion" @click="showCompletion = false">
          <i class="far fa-times-circle fa-4x"></i>
          <p>{{ node.typeData.cancelLinkText }}</p>
        </button>
      </div>
    </tapestry-modal>
    <button class="button-scroll-top" @click="scrollToTop">
      <i class="fas fa-chevron-up fa-2x"></i>
    </button>
    <h1 class="title">{{ node.title }}</h1>
    <accordion-row
      v-for="(row, index) in rows"
      :key="row.id"
      :visible="index === activeIndex"
    >
      <template v-slot:trigger>
        <button
          class="button-row"
          :disabled="lockRows && disabledFrom >= 0 && index > disabledFrom"
          @click="toggle(index)"
        >
          <i :class="index === activeIndex ? 'fas fa-minus' : 'fas fa-plus'"></i>
          {{ row.title }}
        </button>
      </template>
      <template v-slot:content>
        <tapestry-media
          :node-id="row.id"
          :dimensions="dimensions"
          :autoplay="false"
          @complete="updateProgress(row.id)"
          @close="toggle(index)"
          @load="updateScrollValues"
        />
      </template>
      <template v-slot:footer>
        <button v-if="row.completed" class="mt-2" @click="next">
          {{ node.typeData.finishButtonText }}
        </button>
      </template>
    </accordion-row>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex"
import TapestryMedia from "../TapestryMedia"
import TapestryModal from "../TapestryModal"
import AccordionRow from "../AccordionRow"
import Scrollbar from "../Scrollbar"

export default {
  name: "accordion-media",
  components: {
    TapestryMedia,
    TapestryModal,
    AccordionRow,
    Scrollbar,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      activeIndex: 0,
      showCompletion: false,
      isMounted: false,
      scrollHeight: 0,
      scrollTop: 0,
      clientHeight: 0,
    }
  },
  computed: {
    ...mapGetters(["getDirectChildren", "getNode"]),
    hasNext() {
      return this.activeIndex < this.rows.length - 1
    },
    rows() {
      return this.getDirectChildren(this.node.id).map(this.getNode)
    },
    dimensions() {
      if (!this.isMounted) {
        return {
          height: 0,
          width: 0,
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
      return this.rows.findIndex(node => !node.completed)
    },
  },
  mounted() {
    this.isMounted = true
    this.updateScrollValues()
  },
  updated() {
    this.updateScrollValues()
  },
  methods: {
    ...mapMutations(["updateNode"]),
    ...mapActions(["completeNode", "updateNodeProgress"]),
    handleScrollDrag(evt) {
      const container = this.$refs.container.getBoundingClientRect()
      const offset = (evt - container.top) / this.clientHeight
      let newValue = offset * this.$refs.container.scrollTopMax
      if (newValue < 0) {
        newValue = 0
      } else if (newValue > this.$refs.container.scrollTopMax) {
        newValue = this.$refs.container.scrollTopMax
      }
      this.scrollTop = newValue
      this.$refs.container.scrollTop = newValue
    },
    updateScrollValues() {
      this.$nextTick(() => {
        this.scrollHeight = this.$el.scrollHeight
        this.clientHeight = this.$el.clientHeight
      })
    },
    handleScroll() {
      this.scrollTop = this.$refs.container.scrollTop
    },
    scrollToTop() {
      const el = this.$refs.container
      if (el) {
        el.scrollTop = 0
      }
    },
    toggle(index) {
      if (this.activeIndex === index) {
        this.activeIndex = -1
      } else {
        this.activeIndex = index
      }
    },
    next() {
      if (this.hasNext) {
        this.activeIndex++
      } else {
        this.showCompletion = true
      }
    },
    updateProgress(rowId) {
      const { accordionProgress } = this.node
      if (!accordionProgress.includes(rowId)) {
        accordionProgress.push(rowId)
        this.updateNodeProgress({
          id: this.node.id,
          progress: accordionProgress.length / this.rows.length,
        })
        this.updateNode({
          id: this.node.id,
          newNode: { accordionProgress },
        })

        if (accordionProgress.length === this.rows.length) {
          this.completeNode(this.node.id)
        }
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
  color: #fff;
  margin-bottom: 1em;
}

.media-container {
  height: 100%;
  overflow: scroll;
  padding: 24px;
  scrollbar-width: none;
  -ms-overflow-style: none;

  &::-webkit-scrollbar {
    width: 0;
    height: 0;
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

.button-row {
  display: flex;
  align-items: center;
  background: none;
  margin: 0;
  width: 100%;
  border-radius: 4px;
  text-align: left;

  i {
    margin-right: 8px;
  }
}

.button-scroll-top {
  cursor: pointer;
  position: absolute;
  right: 24px;
  bottom: 24px;
  background: #262626;
  border-radius: 50%;
  padding: 0;
  width: 56px;
  height: 56px;
  z-index: 10;
}
</style>
