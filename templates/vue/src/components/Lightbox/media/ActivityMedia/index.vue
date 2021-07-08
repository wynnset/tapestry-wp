<template>
  <div ref="activity" class="activity-media">
    <h1 v-if="showTitle" class="media-title">{{ node.title }}</h1>
    <completion-screen v-if="showCompletionScreen" :question="activeQuestion">
      <button
        v-if="hasNext"
        class="button-completion"
        data-qa="completion-next-button"
        @click="next"
      >
        <i class="fas fa-arrow-circle-right fa-4x"></i>
        <p>Next question</p>
      </button>
      <button v-else class="button-completion" @click="close">
        <i class="far fa-times-circle fa-4x"></i>
        <p>Done</p>
      </button>
    </completion-screen>
    <question
      v-else
      :question="activeQuestion"
      :node="node"
      @submit="handleSubmit"
      @back="$emit('close')"
    ></question>
    <footer v-if="!showCompletionScreen" class="question-footer">
      <p class="question-step">{{ currentQuestionText }}</p>
      <button
        v-if="questions.length > 1"
        class="button-nav"
        :disabled="!hasPrev"
        @click="prev"
      >
        <i class="fas fa-arrow-left"></i>
      </button>
      <button
        v-if="questions.length > 1"
        class="button-nav"
        :disabled="!hasNext"
        @click="next"
      >
        <i class="fas fa-arrow-right"></i>
      </button>
    </footer>
  </div>
</template>

<script>
import client from "@/services/TapestryAPI"
import Question from "./Question"
import CompletionScreen from "./CompletionScreen"
import { mapActions, mapGetters } from "vuex"
import * as wp from "@/services/wp"

export default {
  name: "activity-media",
  components: {
    CompletionScreen,
    Question,
  },
  props: {
    node: {
      type: Object,
      required: true,
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
  },
  data() {
    return {
      activeQuestionIndex: 0,
      showCompletionScreen: false,
    }
  },
  computed: {
    ...mapGetters(["getAnswers"]),
    showTitle() {
      return this.context === "page" && this.node.typeData.showTitle !== false
    },
    questions() {
      return this.node.typeData.activity.questions
    },
    activeQuestion() {
      return this.questions[this.activeQuestionIndex]
    },
    currentQuestionText() {
      return `${this.activeQuestionIndex + 1}/${this.questions.length}`
    },
    hasNext() {
      return this.activeQuestionIndex !== this.questions.length - 1
    },
    hasPrev() {
      return this.activeQuestionIndex !== 0
    },
  },
  mounted() {
    this.$emit("change:dimensions", {
      width: this.dimensions.width,
      height: this.$refs.activity.clientHeight - 100,
    })
    this.$emit("load")
  },
  created() {
    this.markQuestionsComplete()
  },
  methods: {
    ...mapActions(["updateNodeProgress"]),
    markQuestionsComplete() {
      for (let i = 0; i < this.questions.length; i++) {
        const currentQuestion = this.questions[i]
        const currentQuestionAnswer = this.getAnswers(
          this.node.id,
          currentQuestion.id
        )
        if (Object.keys(currentQuestionAnswer).length === 0) {
          currentQuestion.completed = false
        } else {
          currentQuestion.completed = true
        }
      }
    },
    handleSubmit() {
      this.showCompletionScreen = true
      const numberCompleted = this.questions.filter(question => question.completed)
        .length
      const progress = numberCompleted / this.node.typeData.activity.questions.length
      if (this.performDyadNodeCheck()) {
        this.updateNodeProgress({ id: this.node.id, progress }).then(() => {
          if (progress === 1) {
            this.$emit("complete")
          }
        })
      }
    },
    next() {
      this.showCompletionScreen = false
      client.recordAnalyticsEvent("user", "next", "activity", this.node.id, {
        from: this.activeQuestionIndex,
        to: this.activeQuestionIndex + 1,
      })
      this.activeQuestionIndex++
    },
    prev() {
      this.showCompletionScreen = false
      client.recordAnalyticsEvent("user", "prev", "activity", this.node.id, {
        from: this.activeQuestionIndex,
        to: this.activeQuestionIndex - 1,
      })
      this.activeQuestionIndex--
    },
    close() {
      client.recordAnalyticsEvent("user", "close", "activity", this.node.id)
      this.$emit("close")
    },
    performDyadNodeCheck() {
      return wp.getCurrentUser().roles.includes("youth") || !this.node.isDyad
    },
  },
}
</script>

<style lang="scss" scoped>
.activity-media {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: space-between;
  width: 100%;
  min-height: 100%;
  background: #111;
  color: #eee;
  z-index: 10;
  padding: 24px;

  .media-title {
    text-align: left;
    font-size: 1.75rem;
    font-weight: 500;
    margin-bottom: 0.9em;

    :before {
      display: none;
    }
  }
}

.question-footer {
  margin-top: 1em;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.button-completion {
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

.question-step {
  margin: 0;
  padding: 0;
  font-weight: bold;
  font-size: 40px;
  color: var(--tyde-blue);
  margin-right: 32px;
}
</style>
