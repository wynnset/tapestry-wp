<template>
  <div class="fromBucketContainer" :data-qa="`user-from-bucket-${bucket.id}`">
    <b class="bucketLabel">{{ bucket.value }}</b>
    <completed-activity-drag-drop-bucket-item
      v-for="item in bucket.itemArray"
      :key="item.id"
      :bucketItem="item"
      :question="question"
      :data-qa="`user-bucket-item-${item.id}`"
    />
  </div>
</template>
<script>
import CompletedActivityDragDropBucketItem from "./CompletedActivityDragDropBucketItem"
export default {
  components: {
    CompletedActivityDragDropBucketItem,
  },
  props: {
    bucket: {
      type: Object,
      required: true,
    },
    question: {
      type: Object,
      required: false,
      default: () => ({}),
    },
  },
}
</script>
<style scoped>
.fromBucketContainer {
  background-color: #009688;
  margin-bottom: 15px;
  width: 80px;
  min-height: 80px;
  border-radius: 25px;
  overflow-wrap: break-word;
}
.bucketLabel {
  font-size: 23px;
}
</style>
