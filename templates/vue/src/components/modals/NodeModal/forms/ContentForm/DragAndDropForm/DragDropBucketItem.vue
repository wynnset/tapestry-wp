<template>
  <div class="container">
    <div>
      <b>New bucket item</b>
      <color-picker
        label="Background color"
        :currentColor="bucketItem.color"
        :data-qa="`bucket-item-backgroundcolor-${bucketItem.id}`"
        @change="handleItemColorChange"
      />
      <b-form-group v-if="useImages">
        <file-upload
          v-model="bucketItem.imageurl"
          input-test-id="node-bucketitem-thumbnail-url"
          :data-qa="`bucket-item-thumbnail-${bucketItem.id}`"
          :show-url-upload="false"
          :show-image-preview="true"
          :compact-mode="true"
          file-types="image/*"
          @isUploading="handleUploadChange"
        />
      </b-form-group>
      <b-form-input
        v-model="bucketItem.text"
        placeholder="Enter Item Text"
        :data-qa="`bucket-item-text-${bucketItem.id}`"
      ></b-form-input>
      <b-button
        v-if="removeItemPresent"
        squared
        variant="outline-danger"
        @click="$emit('remove')"
      >
        Remove item
      </b-button>
    </div>
  </div>
</template>

<script>
import FileUpload from "@/components/modals/common/FileUpload"
import ColorPicker from "@/components/modals/common/ColorPicker"
export default {
  components: {
    FileUpload,
    ColorPicker,
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
    bucketItem: {
      type: Object,
      required: true,
    },
    removeItemPresent: {
      type: Boolean,
      required: true,
    },
    useImages: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      fromBucketItem: [
        {
          id: 1,
          color: "#808080",
          imageurl: "",
          text: "",
        },
      ],
      nextFromBucketId: 2,
    }
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
  methods: {
    handleUploadChange(state) {
      this.$root.$emit("node-modal::uploading", state)
    },
    handleItemColorChange(color) {
      this.bucketItem.color = color
    },
  },
}
</script>

<style scoped>
.container {
  border: solid;
  background-color: #add8e6;
  margin-bottom: 15px;
  border-radius: 15px;
  margin-left: 0px;
}
</style>
