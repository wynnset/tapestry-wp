<template>
  <div class="container">
    <b-form-group :class="bucketClass">
      <b>New bucket</b>
      <b-form-group>
        <drag-drop-bucket-item
          v-for="(item, index) in bucket.itemArray"
          :key="item.id"
          :node="node"
          :question="question"
          :bucketItem="item"
          :useImages="useImages"
          :data-qa="`bucket-item-${bucket.id}-${item.id}`"
          :removeItemPresent="removeButtonItemPresent"
          @remove="bucket.itemArray.splice(index, 1)"
        />
      </b-form-group>
      <b-button
        v-if="isFromBucket"
        class="addButton"
        variant="primary"
        squared
        :data-qa="`add-bucket-item-button-${bucket.id}`"
        @click="$emit('add')"
      >
        Add a item
      </b-button>
    </b-form-group>
    <b-form-group>
      <b-form-group v-if="isFromBucket">
        <b-form-input
          v-model="question.answerTypes.dragDrop.fromBucketArray[index].value"
          placeholder="Enter from bucket label"
          :data-qa="`from-bucket-label-${bucket.id}`"
        ></b-form-input>
      </b-form-group>
      <b-form-group v-else>
        <b-form-input
          v-model="question.answerTypes.dragDrop.toBucketArray[index].value"
          placeholder="Enter to bucket label"
          :data-qa="`to-bucket-label-${bucket.id}`"
        ></b-form-input>
      </b-form-group>
      <b-form-group v-if="isFromBucket">
        <b-button
          v-if="removeButtonFromBucketPresent"
          squared
          variant="outline-danger"
          @click="$emit('remove')"
        >
          Remove bucket
        </b-button>
      </b-form-group>
      <b-form-group v-else>
        <b-button
          v-if="removeButtonToBucketPresent"
          squared
          variant="outline-danger"
          @click="$emit('remove')"
        >
          Remove bucket
        </b-button>
      </b-form-group>
    </b-form-group>
  </div>
</template>

<script>
import DragDropBucketItem from "./DragDropBucketItem"
export default {
  components: {
    DragDropBucketItem,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
    question: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    bucket: {
      type: Object,
      required: true,
    },
    isFromBucket: {
      type: Boolean,
      required: false,
    },
    fromBucketArray: {
      type: Array,
      required: true,
    },
    toBucketArray: {
      type: Array,
      required: true,
    },
    useImages: {
      type: Boolean,
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
    removeButtonItemPresent() {
      return this.bucket.itemArray.length > 1
    },
    removeButtonFromBucketPresent() {
      return this.fromBucketArray.length > 1
    },
    removeButtonToBucketPresent() {
      return this.toBucketArray.length > 1
    },
  },
}
</script>

<style scoped>
.container {
  display: flex;
  align-items: center;
}
.fromBucketContainer {
  background-color: #e0f2f1;
  margin-bottom: 15px;
  border-radius: 15px;
  margin-left: -15px;
  min-height: 200px;
}
.toBucketContainer {
  background-color: #e8eaf6;
  min-height: 200px;
  margin-bottom: 15px;
  margin-left: -15px;
  border-radius: 15px;
}
</style>
