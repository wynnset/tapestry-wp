<template>
  <activity-screen
    v-if="showActivityScreen"
    :id="node.id"
    @back="showActivityScreen = false"
    @close="$emit('close')"
  />
  <div v-else data-qa="end-screen" class="end-screen">
    <button v-if="showQuizButton" @click="handleClick($event, 'show-quiz')">
      <i class="fas fa-question-circle fa-4x"></i>
      <p>{{ buttonText }}</p>
    </button>
    <button @click="handleClick($event, 'rewatch')">
      <i class="fas fa-redo fa-4x"></i>
      <p>Rewatch</p>
    </button>
    <button @click="handleClick($event, 'close')">
      <i class="far fa-times-circle fa-4x"></i>
      <p>Close</p>
    </button>
  </div>
</template>

<script>
import client from "@/services/TapestryAPI"
import ActivityScreen from "./ActivityScreen"

export default {
  name: "end-screen",
  components: {
    ActivityScreen,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      showActivityScreen: false,
    }
  },
  computed: {
    showQuizButton() {
      return Boolean(this.node.quiz && this.node.quiz.length)
    },
    buttonText() {
      const allDone = this.node.quiz.every(question => question.completed)
      return allDone ? "Reanswer Question" : "Answer Question"
    },
  },
  methods: {
    handleClick(evt, type) {
      client.recordAnalyticsEvent("user", "click", "end-screen", this.node.id, {
        x: evt.clientX,
        y: evt.clientY,
      })

      if (type === "show-quiz") {
        this.showActivityScreen = true
      } else {
        this.$emit(type)
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.end-screen {
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: #111;
  color: #eee;
  opacity: 1;
  transition: opacity 0.4s ease-out;
  z-index: 10;
  border-radius: 1px;

  button {
    background: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: inherit;
    margin-right: 3em;

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
}
</style>
