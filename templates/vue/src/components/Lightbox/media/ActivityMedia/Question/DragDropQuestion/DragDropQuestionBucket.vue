<template>
  <div>
    <div
      v-if="isFromBucket"
      class="fromBucketContainer"
      :data-qa="`user-from-bucket-${bucket.id}`"
      @dragover.prevent
      @dragenter.prevent
      @drop.prevent="drop($event, bucket)"
    >
      <b class="bucketLabel">{{ bucket.value }}</b>
      <drag-drop-question-bucket-item
        v-for="item in bucket.itemArray"
        :key="item.id"
        :node="node"
        :question="question"
        :bucketItem="item"
        :parentBucket="bucket"
        :isFromBucketItem="true"
        :data-qa="`user-bucket-item-${item.id}`"
      />
    </div>
    <div
      v-else
      class="toBucketContainer"
      :data-qa="`user-to-bucket-${bucket.id}`"
      @dragover.prevent
      @dragenter.prevent
      @drop.prevent="drop($event, bucket)"
    >
      <b class="bucketLabel">{{ bucket.value }}</b>
      <drag-drop-question-bucket-item
        v-for="item in bucket.itemArray"
        :key="item.id"
        :node="node"
        :question="question"
        :bucketItem="item"
        :parentBucket="bucket"
        :isFromBucketItem="false"
        :data-qa="`user-bucket-item-${item.id}`"
      />
    </div>
  </div>
</template>

<script>
import DragDropQuestionBucketItem from "./DragDropQuestionBucketItem"
export default {
  components: {
    DragDropQuestionBucketItem,
  },
  props: {
    question: {
      type: Object,
      required: true,
    },
    node: {
      type: Object,
      required: true,
    },
    isFromBucket: {
      type: Boolean,
      required: false,
    },
    bucket: {
      type: Object,
      required: true,
    },
    fromBucketArray: {
      type: Array,
      required: true,
    },
    toBucketArray: {
      type: Array,
      required: true,
    },
  },
  computed: {
    bucketClass() {
      if (this.isFromBucket) {
        return "fromBucketContainer"
      } else {
        return "toBucketContainer"
      }
    },
  },
  created() {
    if (
      !this.question.answerTypes.dragDrop.hasOwnProperty("fromBucketArray") ||
      !this.question.answerTypes.dragDrop.hasOwnProperty("toBucketArray")
    ) {
      this.question.answerTypes.dragDrop.fromBucketArray = this.fromBucketArray
      this.question.answerTypes.dragDrop.toBucketArray = this.toBucketArray
    } else {
      this.fromBucketArray = this.question.answerTypes.dragDrop.fromBucketArray
      this.toBucketArray = this.question.answerTypes.dragDrop.toBucketArray
    }
  },
  methods: {
    drop: function(e, bucket) {
      const itemId = e.dataTransfer.getData("itemId")
      const parentBucketId = e.dataTransfer.getData("parentBucketId")
      const booleanValue = e.dataTransfer.getData("isFromBucketItem")
      const isFromBucket = booleanValue === "true"
      if (isFromBucket) {
        let parentBucket = this.findBucketInFromBucketArray(parentBucketId)
        let item = this.findItemInParentBucketArray(parentBucket, itemId)
        bucket.itemArray.push(item)
      } else {
        let parentBucket = this.findBucketInToBucketArray(parentBucketId)
        let item = this.findItemInParentBucketArray(parentBucket, itemId)
        bucket.itemArray.push(item)
      }
    },
    findBucketInFromBucketArray: function(parentBucketId) {
      let foundBucket = ""
      for (let i = 0; i < this.fromBucketArray.length; i++) {
        if (this.fromBucketArray[i].id === Number(parentBucketId)) {
          foundBucket = this.fromBucketArray[i]
        }
      }
      return foundBucket
    },
    findItemInParentBucketArray: function(parentBucket, itemId) {
      let foundItem = ""
      for (let i = 0; i < parentBucket.itemArray.length; i++) {
        if (parentBucket.itemArray[i].id === Number(itemId)) {
          foundItem = parentBucket.itemArray[i]
          parentBucket.itemArray.splice(i, 1)
        }
      }
      return foundItem
    },
    findBucketInToBucketArray: function(parentBucketId) {
      let foundBucket = ""
      for (let i = 0; i < this.toBucketArray.length; i++) {
        if (this.toBucketArray[i].id === Number(parentBucketId)) {
          foundBucket = this.toBucketArray[i]
        }
      }
      return foundBucket
    },
  },
}
</script>

<style scoped>
.fromBucketContainer {
  background-color: #009688;
  margin-bottom: 15px;
  min-height: 350px;
  padding-bottom: 100px;
  border-radius: 25px;
  overflow-wrap: break-word;
}
.toBucketContainer {
  background-color: #3f51b5;
  margin-bottom: 15px;
  min-height: 250px;
  padding-bottom: 100px;
  border-radius: 25px;
  overflow-wrap: break-word;
}
.bucketLabel {
  font-size: 28px;
}
</style>
