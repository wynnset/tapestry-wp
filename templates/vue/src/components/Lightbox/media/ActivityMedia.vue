<template>
  <div>
    <h1 v-if="showTitle" class="media-title">{{ node.title }}</h1>
    <activity-screen
      :id="node.id"
      style="position: relative;"
      @submit="handleSubmit"
      @back="$emit('close')"
      @close="$emit('close')"
    />
  </div>
</template>

<script>
import { mapActions } from "vuex"
import ActivityScreen from "./common/ActivityScreen"

export default {
  name: "activity-media",
  components: {
    ActivityScreen,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
    context: {
      type: String,
      required: false,
      default: "",
    },
    showTitle: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  mounted() {
    this.$emit("load")
  },
  methods: {
    ...mapActions(["updateNodeProgress"]),
    handleSubmit() {
      const numberCompleted = this.node.quiz.filter(question => question.completed)
        .length
      const progress = numberCompleted / this.node.quiz.length
      this.updateNodeProgress({ id: this.node.id, progress }).then(() => {
        if (progress === 1) {
          this.$emit("complete")
        }
      })
    },
  },
}
</script>

<style lang="scss" scoped>
.media-title {
  text-align: left;
  font-size: 1.75rem;
  font-weight: 500;
  margin-bottom: 0.5em;

  :before {
    display: none;
  }
}
</style>
