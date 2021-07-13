<template>
  <b-form class="grid-container">
    <div v-if="question.answerTypes.text.allowMultiple" class="list">
      <b-input-group
        v-for="(answerItem, index) in textAnswers"
        :key="index"
        data-qa="list-input-list"
        class="mt-2 list-input-list"
      >
        <b-form-input
          v-model="textAnswers[index]"
          :data-qa="`list-input-${index}`"
          :placeholder="getListPlaceholder(index)"
          autofocus
          @keydown.enter="addAnswer"
        ></b-form-input>
        <b-input-group-append v-if="minFields !== maxFields">
          <b-button
            :variant="numOfAnswers <= minFields ? 'secondary' : 'danger'"
            :data-qa="`list-del-${index}`"
            :disabled="numOfAnswers <= minFields"
            @click="deleteAnswer(index)"
          >
            -
          </b-button>
        </b-input-group-append>
        <b-input-group-append v-if="minFields != maxFields">
          <b-button
            :variant="numOfAnswers >= maxFields ? 'secondary' : 'primary'"
            :data-qa="`list-add-${index}`"
            :disabled="numOfAnswers >= maxFields"
            @click="addAnswer"
          >
            +
          </b-button>
        </b-input-group-append>
      </b-input-group>
    </div>
    <b-form-textarea
      v-else-if="question.answerTypes.text.isMultiLine"
      v-model="textAnswers[0]"
      rows="5"
    ></b-form-textarea>
    <b-form-input
      v-else
      v-model="textAnswers[0]"
      :placeholder="question.answerTypes.text.placeholder"
    ></b-form-input>
    <b-form-invalid-feedback :state="isAnswerValid">
      Please enter a response for all inputs.
    </b-form-invalid-feedback>
    <div>
      <b-button class="submit-btn" variant="primary" @click="handleTextSubmit">
        Submit
      </b-button>
    </div>
  </b-form>
</template>

<script>
export default {
  name: "text-question",
  props: {
    question: {
      type: Object,
      required: true,
    },
    answer: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      isAnswerValid: true,
      textAnswers: this.answer.map(x => x),
    }
  },
  computed: {
    minFields() {
      return parseInt(this.question.answerTypes.text.minFields, 10)
    },
    maxFields() {
      return parseInt(this.question.answerTypes.text.maxFields, 10)
    },
    numOfAnswers() {
      return this.textAnswers.length
    },
  },
  created() {
    if (this.question.answerTypes.text.allowMultiple) {
      for (let i = this.numOfAnswers; i < this.minFields; i++) {
        this.addAnswer()
      }
    }
    if (this.answer.length > 1 && !this.question.answerTypes.text.allowMultiple) {
      this.textAnswers = []
      this.textAnswers.push(this.answer.join())
    }
  },
  methods: {
    handleTextSubmit(event) {
      event.preventDefault()
      this.isAnswerValid = this.textAnswers.every(answer => answer !== "")
      if (this.isAnswerValid) {
        this.$emit("submit", this.textAnswers)
      }
    },
    deleteAnswer(index) {
      if (this.textAnswers[index]?.length) {
        this.$bvModal
          .msgBoxConfirm("This answer will be removed.", {
            modalClass: "node-modal-confirmation",
            title: "Are you sure you want to delete this answer from the list?",
            okTitle: "Yes, delete!",
            okVariant: "danger",
          })
          .then(close => {
            if (close) {
              this.textAnswers.splice(index, 1)
            }
          })
          .catch(err => console.log(err))
      } else {
        this.textAnswers.splice(index, 1)
      }
    },
    addAnswer() {
      if (this.numOfAnswers < this.maxFields) {
        this.textAnswers.push("")
      }
    },
    getListPlaceholder(i) {
      if (this.question.answerTypes.text.placeholder) {
        return this.question.answerTypes.text.placeholder
      } else {
        i++
        const nth = function(d) {
          if (d > 3 && d < 21) return "th"
          switch (d % 10) {
            case 1:
              return "st"
            case 2:
              return "nd"
            case 3:
              return "rd"
            default:
              return "th"
          }
        }
        return "Type your " + i + nth(i) + " answer"
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.list-input-list {
  border-radius: 5px;
  overflow: hidden;
  .input-group-append button {
    min-width: 45px;
  }
}
.submit-btn {
  float: right;
  margin-top: 30px;
  width: 15%;
}
</style>
