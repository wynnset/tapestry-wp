<template>
  <div
    class="container"
    draggable="true"
    @dragstart="dragStart($event, bucketItem, parentBucket, isFromBucketItem)"
  >
    <div
      class="circle"
      :style="{
        'background-image': hasImage ? 'url(' + bucketItem.imageurl + ')' : 'none',
        'background-color': bucketItem.color,
      }"
    ></div>
    <b v-if="!question.answerTypes.dragDrop.hideText">{{ bucketItem.text }}</b>
  </div>
</template>

<script>
export default {
  props: {
    node: {
      type: Object,
      required: true,
    },
    question: {
      type: Object,
      required: true,
    },
    bucketItem: {
      type: Object,
      required: true,
    },
    parentBucket: {
      type: Object,
      required: true,
    },
    isFromBucketItem: {
      type: Boolean,
      required: false,
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
    hasImage() {
      return this.question.answerTypes.dragDrop.useImages
    },
  },
  methods: {
    dragStart: function(e, item, parentBucket, isFromBucketItem) {
      e.dataTransfer.dropEffect = "move"
      e.dataTransfer.effectAllowed = "move"
      e.dataTransfer.setData("itemId", item.id)
      e.dataTransfer.setData("parentBucketId", parentBucket.id)
      e.dataTransfer.setData("isFromBucketItem", isFromBucketItem)
    },
  },
}
</script>

<style scoped>
.circle {
  border-radius: 100%;
  min-height: 140px;
  border: solid;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  text-align: center;
  margin: auto;
}
.container {
  margin-top: 30px;
  margin-bottom: 30px;
  cursor: pointer;
}
</style>
