<template>
  <b-container class="completed-activity-media">
    <b-row align-v="center" style="min-height:150px;">
      <b-col v-if="type === 'text' && !isListTextType" align-self="center">
        <div class="text">
          {{ answerData }}
        </div>
      </b-col>
      <b-col v-if="type === 'text' && isListTextType">
        <ol>
          <li v-for="answer in answerData" :key="answer.index">
            {{ answer }}
          </li>
        </ol>
      </b-col>
      <b-col v-if="type === 'audio'" align-self="center">
        <audio controls :src="urlAnswer"></audio>
      </b-col>
      <b-col v-if="type === 'multipleChoice'" align-self="center">
        <ul>
          <li v-for="answer in answerData" :key="answer.index">
            <completed-multiple-choice-item
              :item="getMultipleChoiceAnswerItem(answer)"
              :useImages="question.answerTypes.multipleChoice.useImages"
            />
          </li>
        </ul>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import CompletedMultipleChoiceItem from "./CompletedMultipleChoiceItem"
import { data as wpData } from "@/services/wp"

export default {
  name: "completed-activity-media",
  components: {
    CompletedMultipleChoiceItem,
  },
  props: {
    type: {
      type: String,
      required: true,
      validator: val => ["text", "audio", "multipleChoice"].includes(val),
    },
    answerData: {
      type: [Object, String, Array, Number],
      required: true,
    },
    question: {
      type: Object,
      required: false,
      default: () => ({}),
    },
  },
  computed: {
    urlAnswer() {
      return (
        wpData.uploadDirArray.baseurl + "/" + this.answerData.url + "?" + Date.now()
      )
    },
    isListTextType() {
      return this.question.answerTypes?.text.allowMultiple
    },
  },
  methods: {
    getMultipleChoiceAnswerItem(id) {
      return this.question.answerTypes.multipleChoice.choices.find(
        option => option.id === id
      )
    },
  },
}
</script>

<style lang="scss" scoped>
.completed-activity-media {
  background: #262626;
  border-radius: 8px;
  margin-bottom: 8px;
  padding: 8px 16px 8px 16px;
  .text {
    text-align: left;
    padding-left: 1em;
    border-left: solid 1px #666;
  }
}
</style>
