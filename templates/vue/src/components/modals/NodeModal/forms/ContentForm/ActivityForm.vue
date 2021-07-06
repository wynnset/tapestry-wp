<template>
  <div class="activity">
    <b-card
      v-for="(question, index) in questions"
      :key="question.id"
      bg-variant="secondary"
      text-variant="light"
      class="mb-3"
    >
      <b-form-group class="mb-0">
        <b-row align-v="center" class="mb-2 mx-0">
          <p
            v-b-toggle="`question-${index}-container`"
            class="font-weight-bold p-0 m-0 question-text"
          >
            {{ getGroupTitle(question, index) }}
            <small>
              (Click to
              <span class="when-open">collapse</span>
              <span class="when-closed">expand</span>
              )
            </small>
          </p>
          <b-button
            class="ml-auto del-button"
            size="sm"
            variant="danger"
            text-variant="white"
            @click="deleteQuestion(question.id)"
          >
            Delete
          </b-button>
        </b-row>
        <b-collapse :id="`question-${index}-container`" visible>
          <b-card
            sub-title="Show answer to a previous activity first"
            bg-variant="light"
            text-variant="dark"
            class="mb-3"
          >
            <b-form-group>
              <b-form-checkbox
                v-model="question.followUp.enabled"
                switch
                @change="togglingFollowUp(question)"
              >
                {{ question.followUp.enabled ? "Yes" : "No" }}
              </b-form-checkbox>
            </b-form-group>
            <b-form-group
              v-if="question.followUp.enabled"
              label="Show this text first:"
            >
              <b-form-input
                v-model="question.followUp.text"
                placeholder="Previously, you said:"
              ></b-form-input>
            </b-form-group>
            <b-form-group
              v-if="question.followUp.enabled"
              label="Then show user answer to the following activity:"
            >
              <combobox
                v-model="question.followUp.questionId"
                class="mb-0"
                :options="getPreviousQuestions(question)"
                item-text="text"
                item-value="id"
                empty-message="There are no questions in this tapestry."
              >
                <template v-slot="slotProps">
                  <p>
                    {{ slotProps.option.text }}
                  </p>
                </template>
              </combobox>
            </b-form-group>
          </b-card>
          <b-card
            sub-title="Question details"
            bg-variant="light"
            text-variant="dark"
            class="mb-3"
          >
            <b-form-group label="Question text">
              <b-form-input
                v-model="question.text"
                :data-testid="`question-title-${index}`"
                :data-qa="`question-text-${index}`"
              />
            </b-form-group>
            <b-card-sub-title class="mt-2 mb-2">Answer Options</b-card-sub-title>
            <b-form-group class="mt-3">
              <b-form-checkbox
                v-model="question.answerTypes.text.enabled"
                :data-qa="`question-answer-text-${index}`"
                switch
              >
                Text entry
              </b-form-checkbox>
              <div v-if="question.answerTypes.text.enabled" class="mt-2 pl-4 ml-2">
                <b-form-radio-group v-model="question.answerTypes.text.isMultiLine">
                  <b-form-radio
                    :data-qa="`question-answer-text-multi-${index}`"
                    name="multi-line"
                    :value="true"
                  >
                    Multi-line
                  </b-form-radio>
                  <b-form-radio
                    :data-qa="`question-answer-text-single-${index}`"
                    name="single-line"
                    :value="false"
                  >
                    Single Line
                  </b-form-radio>
                </b-form-radio-group>
                <div
                  v-if="
                    question.answerTypes.text.enabled &&
                      !question.answerTypes.text.isMultiLine
                  "
                  class="mt-2 pl-4"
                >
                  <label for="placeholder">Placeholder (optional):</label>
                  <b-form-input
                    id="placeholder"
                    v-model="question.answerTypes.text.placeholder"
                    :data-qa="`question-answer-text-single-placeholder-${index}`"
                  ></b-form-input>
                </div>
              </div>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox
                v-model="question.answerTypes.audio.enabled"
                :data-qa="`question-answer-audio-${index}`"
                switch
              >
                Audio recorder
              </b-form-checkbox>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox
                v-model="question.answerTypes.dragDrop.enabled"
                data-qa="question-answer-dragdrop"
                switch
              >
                Drag and drop
              </b-form-checkbox>
              <div class="mt-2 pl-4 ml-2">
                <drag-drop-form
                  v-if="question.answerTypes.dragDrop.enabled"
                  :node="node"
                  :question="question"
                />
              </div>
            </b-form-group>
          </b-card>
          <b-card
            sub-title="Confirmation customization"
            bg-variant="light"
            text-variant="dark"
          >
            <b-form-group label="Title">
              <b-form-input
                v-model="question.confirmation.title"
                :data-testid="`question-confirmation-title-${index}`"
                placeholder="Thanks!"
              />
            </b-form-group>
            <rich-text-form
              v-model="question.confirmation.message"
              :data-testid="`question-confirmation-message-${index}`"
              placeholder="Your response has been recorded."
            />
          </b-card>
        </b-collapse>
      </b-form-group>
    </b-card>
    <b-row class="mx-0">
      <b-button variant="primary" @click="addQuestion">
        <i class="fas fa-plus icon"></i>
        Add Question
      </b-button>
    </b-row>
  </div>
</template>

<script>
import { mapState } from "vuex"
import Combobox from "@/components/modals/common/Combobox"
import Helpers from "@/utils/Helpers"
import RichTextForm from "./RichTextForm"
import DragDropForm from "./DragAndDropForm"

const defaultQuestion = {
  text: "",
  followUp: {
    enabled: false,
    text: "",
    nodeId: null,
    questionId: null,
  },
  answerTypes: {
    text: {
      enabled: false,
      placeholder: "",
      isMultiLine: false,
    },
    audio: {
      enabled: false,
    },
    dragDrop: {
      enabled: false,
    },
  },
  confirmation: {
    title: "",
    message: "",
  },
  completed: false,
}

export default {
  components: {
    Combobox,
    RichTextForm,
    DragDropForm,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      questions: this.node.typeData.activity?.questions || [],
    }
  },
  computed: {
    ...mapState(["nodes"]),
  },
  watch: {
    questions(newQuestions) {
      this.$set(this.node.typeData.activity, "questions", newQuestions)
    },
  },
  created() {
    if (!this.node.typeData.activity) {
      this.node.typeData.activity = {
        questions: [],
      }
    }
  },
  methods: {
    getPreviousQuestions(currentQuestion) {
      const allQuestions = Object.values(this.nodes)
        .filter(node => Boolean(node.typeData.activity?.questions))
        .flatMap(node => node.typeData.activity.questions)
      return allQuestions.filter(qn => qn.id !== currentQuestion.id)
    },
    addQuestion() {
      this.questions = [
        ...this.questions,
        { ...Helpers.deepCopy(defaultQuestion), id: Helpers.createUUID() },
      ]
    },
    deleteQuestion(id) {
      this.questions = this.questions.filter(question => question.id !== id)
    },
    getGroupTitle(question, index) {
      return `Question #${index + 1}: ${question.text || "Untitled"}`
    },
    togglingFollowUp(question) {
      question.followUp.text = ""
      question.followUp.questionId = ""
      question.followUp.nodeId = ""
    },
  },
}
</script>

<style lang="scss" scoped>
.activity {
  .icon {
    margin-right: 4px;
  }

  .question-text {
    flex: 10;
  }

  .del-button {
    flex: 1;
  }

  .collapsed .when-open,
  .not-collapsed .when-closed {
    display: none;
  }
}
</style>
