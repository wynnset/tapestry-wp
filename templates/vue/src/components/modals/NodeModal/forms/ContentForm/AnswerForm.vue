<template>
  <div>
    <b-form-group data-qa="activity-combobox" label="Activity">
      <combobox
        v-model="node.typeData.activityId"
        :options="activityNodes"
        data-qa="choose-activity-node"
        item-text="title"
        item-value="id"
        empty-message="Please select an Activity first"
      >
        <template v-slot="slotProps">
          <p>
            {{ slotProps.option.title }}
          </p>
        </template>
      </combobox>
    </b-form-group>
    <b-form-group data-qa="question-select" label="Question">
      <combobox
        v-model="node.typeData.questionId"
        :options="availableQuestions"
        data-qa="choose-question"
        item-text="text"
        item-value="id"
        empty-message="Please select an activity first."
      >
        <template v-slot="slotProps">
          <p>
            {{ slotProps.option.text }}
          </p>
        </template>
      </combobox>
    </b-form-group>
    <b-form-group label="Show this text first">
      <b-form-input
        v-if="node.typeData.questionId"
        v-model="node.typeData.precedingText"
        data-qa="follow-up-text"
        :placeholder="originalQuestionText"
        description="If empty, will default to the original question text"
      ></b-form-input>
    </b-form-group>
  </div>
</template>

<script>
import { mapState } from "vuex"
import Combobox from "@/components/modals/common/Combobox"
export default {
  components: {
    Combobox,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
  },
  computed: {
    ...mapState(["nodes"]),
    activityNodes() {
      return Object.values(this.nodes).filter(node => node.mediaType == "activity")
    },
    availableQuestions() {
      return Object.values(this.activityNodes)
        .filter(node => node.id == this.node.typeData.activityId)
        .flatMap(node => node.typeData.activity.questions)
    },
    originalQuestionText() {
      return this.availableQuestions.find(
        question => question.id === this.node.typeData.questionId
      )?.text
    },
  },
}
</script>
