<template>
  <div :id="`stage-${nodeId}`" class="stage-wrapper" :style="nodeStyles">
    <div>
      <div class="stage-header">
        <tyde-progress-bar :node-id="this.selectedModuleId" />
        <div class="stage-star">
          <img :src="this.done ? this.activeStarSrc : this.inactiveStarSrc" />
          <div v-if="!this.done">
            {{ this.numComplete }}/{{ this.topics.length }}
          </div>
        </div>
        <h1>{{ node.title }}</h1>
        <tyde-button
          class="close-button"
          icon="times"
          @click="$emit('close')"
        ></tyde-button>
      </div>
      <section>
        <tyde-star-celebration v-if="showStar" @close="showStar = false" />
        <tyde-topic
          v-for="topic in topics"
          :key="topic.id"
          :topic="topic"
          :show-complete="true"
          style="align-self: flex-start;"
          @click="openLightbox(topic.id)"
        />
      </section>
      <footer>
        <tyde-button
          :disabled="!stageIndex"
          label="Prev"
          @click="$emit(stageIndex ? 'prev' : '')"
        ></tyde-button>
        <tyde-button
          :disabled="!done"
          label="Next"
          @click="$emit(done ? 'next' : '')"
        ></tyde-button>
      </footer>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapState } from "vuex"
import TydeButton from "./TydeButton"
import TydeTopic from "./TydeTopic"
import TydeProgressBar from "./TydeProgressBar"
import TydeStarCelebration from "./TydeStarCelebration"
import ActiveStar from "@/assets/star-active.png"
import InactiveStar from "@/assets/star-inactive.png"
import Helpers from "@/utils/Helpers"
import { tydeTypes } from "@/utils/constants"

export default {
  name: "tyde-stage",
  components: {
    TydeButton,
    TydeTopic,
    TydeProgressBar,
    TydeStarCelebration,
  },
  props: {
    nodeId: {
      type: [String, Number],
      required: true,
    },
    stageIndex: {
      type: [Number],
      default: 1,
    },
  },
  data() {
    return {
      notCompleted: true,
      showStar: false,
    }
  },
  watch: {
    isLightboxOpen() {
      if (this.notCompleted && this.getNode(this.nodeId).tydeProgress == 1) {
        this.showStar = true
        this.notCompleted = false
      }
    },
  },
  computed: {
    ...mapGetters(["getNode", "getDirectChildren"]),
    ...mapState(["selectedModuleId"]),
    done() {
      return this.topics.every(topic => topic.completed)
    },
    node() {
      return this.getNode(this.nodeId)
    },
    nodeStyles() {
      return {
        backgroundImage: `url(${this.node.imageURL})`,
      }
    },
    topics() {
      const childrenIds = this.getDirectChildren(this.nodeId)
      return childrenIds.map(id => this.getNode(id))
    },
    activeStarSrc() {
      return Helpers.getImagePath(ActiveStar)
    },
    inactiveStarSrc() {
      return Helpers.getImagePath(InactiveStar)
    },
    numComplete() {
      const reducer = (accumulator, completed) =>
        accumulator + (completed === true ? 1 : 0)
      return this.topics.map(n => n.completed).reduce(reducer, 0)
    },
    isLightboxOpen() {
      return this.$route.path.includes("nodes")
    },
  },
  mounted() {
    document.querySelector("body").classList.add("tapestry-stage-open")
    this.notCompleted = this.getNode(this.nodeId).tydeProgress !== 1
  },
  beforeDestroy() {
    document.querySelector("body").classList.remove("tapestry-stage-open")
  },
  methods: {
    getImageStyles(index) {
      if (index === 0 || index === 3) {
        return { width: "70%" }
      }
      return {}
    },
    openLightbox(id) {
      this.$router.push(`/nodes/${id}`)
    },
    closeLightbox() {
      this.$router.push(`/`)
    },
  },
}
</script>

<style lang="scss">
body.tapestry-stage-open {
  overflow: hidden;
}
</style>

<style lang="scss" scoped>
.stage-wrapper {
  background-size: cover;
  height: 100%;
  width: 100%;

  .stage-header {
    display: flex;
    flex-direction: row;
    width: 100vw;
    top: 32px;
    right: 32px;
    font-family: "Roboto", monospace;
    color: var(--tyde-border-green);
    align-items: center;

    .stage-star {
      margin-left: 32vw;
      margin-right: 30px;
      padding-bottom: 10px;
      position: relative;
      font-family: inherit;
      color: white;
      font-size: 29px;

      img {
        width: 80px;
      }

      > div {
        position: absolute;
        left: 27%;
        top: 24%;
      }
    }

    h1 {
      font-family: inherit;
      font-size: 64px;

      &::before {
        display: none;
      }
    }

    .close-button {
      position: absolute;
      top: -25px;
      right: -25px;
      z-index: 20;
    }
  }

  > div {
    width: 100vw;
    height: 72vh;
    position: absolute;
    top: 32px;
    right: 32px;
    font-family: "Roboto", monospace;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 64px;
    color: var(--tyde-border-green);

    > section {
      margin-left: 30vw;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: inherit;

      button {
        margin: 0;
        background: none;
        color: inherit;
        align-self: flex-start;

        &:hover p {
          transform: translateY(-8px);
        }

        div {
          width: 198px;
          height: 184px;

          img {
            width: 90%;
            height: auto;
          }
        }

        p {
          padding: 0;
          margin: 0.5em 0 0;
          font-family: inherit;
          color: inherit;
          font-size: 32px;
          transition: transform 0.2s ease-out;
          line-height: 0.9em;
          max-width: 200px;
        }
      }
    }
  }

  footer {
    margin-right: 11vw;
    display: flex;
    justify-content: flex-end;
    margin-top: 80px;
  }
}
</style>
