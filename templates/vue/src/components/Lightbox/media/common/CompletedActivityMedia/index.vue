<template>
  <b-container class="completed-activity-media">
    <b-row align-v="center" style="min-height:150px;">
      <b-col v-if="showIcon" align-self="center" cols="2">
        <div v-if="type === 'dragDrop'" class="dragdropicon">
          <img :src="dragDropIcon" />
        </div>
        <tapestry-icon :icon="type" />
      </b-col>
      <b-col v-if="type === 'text'" align-self="center">
        <div class="text">
          {{ answerData }}
        </div>
      </b-col>
      <b-col v-if="type === 'audio'" align-self="center">
        <audio controls :src="urlAnswer"></audio>
      </b-col>
      <b-col v-if="type === 'dragDrop'" align-self="center">
        <ul class="flexContainer">
          <li
            v-for="answer in getDragDropBuckets"
            :key="answer.index"
            class="flexItem"
          >
            <completed-activity-drag-drop-bucket
              :bucket="answer"
              :question="question"
            />
          </li>
        </ul>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import CompletedActivityDragDropBucket from "./CompletedActivityDragDropBucket"
import TapestryIcon from "@/components/common/TapestryIcon"
import DragDropIcon from "@/assets/icons/drag_drop.svg"
import { data as wpData } from "@/services/wp"

export default {
  name: "completed-activity-media",
  components: {
    TapestryIcon,
    CompletedActivityDragDropBucket,
  },
  props: {
    type: {
      type: String,
      required: true,
      validator: val => ["text", "audio", "dragDrop"].includes(val),
    },
    answerData: {
      type: [String, Array, Object],
      required: true,
    },
    question: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    showIcon: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  computed: {
    dragDropIcon() {
      return `${wpData.vue_uri}/${DragDropIcon.split("dist")[1]}`
    },
    getDragDropBuckets() {
      return this.answerData.toBucketArray.filter(
        toBucket => toBucket.itemArray.length > 0
      )
    },
    urlAnswer() {
      return (
        wpData.uploadDirArray.baseurl + "/" + this.answerData.url + "?" + Date.now()
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

.flexContainer {
  display: flex;
  margin-left: 30px;
}
.flexItem {
  margin-right: 20px;
}

.dragdropicon {
  height: 30px;
  width: 30px;
}
</style>
