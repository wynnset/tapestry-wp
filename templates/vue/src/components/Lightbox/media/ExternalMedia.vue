<template>
  <div>
    <h1 v-if="showTitle" class="external-media-title external-page-style">
      {{ node.title }}
    </h1>
    <div class="external-media-container">
      <iframe
        v-if="node.behaviour === 'embed'"
        id="external"
        frameborder="0"
        allowfullscreen="allowfullscreen"
        :src="normalizedUrl"
        :style="'min-height:' + dimensions.height + 'px'"
        @load="$emit('load')"
      ></iframe>
      <div v-else class="preview" :style="previewStyles">
        <div
          class="preview-image"
          :style="{ 'background-image': `url(${node.typeData.linkMetadata.image})` }"
        >
          <a :href="node.typeData.mediaURL" target="blank"></a>
        </div>
        <div class="preview-content">
          <h1>
            <a :href="node.typeData.mediaURL" target="blank">
              {{ node.typeData.linkMetadata.title }}
            </a>
          </h1>
          <p>{{ node.typeData.linkMetadata.description }}</p>
          <p>
            <a :href="node.typeData.mediaURL" target="blank">Open link</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Helpers from "@/utils/Helpers"

export default {
  name: "external-media",
  props: {
    dimensions: {
      type: Object,
      required: false,
      default: () => ({}),
    },
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
      default: true,
    },
  },
  computed: {
    normalizedUrl() {
      return Helpers.normalizeUrl(this.node.typeData.mediaURL)
    },
    previewStyles() {
      const { height } = this.dimensions
      if (height) {
        return {
          maxHeight: `${height - 32}px`,
        }
      }
      return {}
    },
  },
  mounted() {
    this.$emit("complete")
    if (this.node.behaviour !== "embed") {
      this.$emit("load")
    }
  },
}
</script>

<style lang="scss" scoped>
.external-media-container {
  height: 100%;
  background: #fff;
}

.preview {
  display: flex;
  height: 100%;
  width: 100%;

  .preview-image {
    cursor: pointer;
    position: relative;
    flex: 1;
    width: auto;
    min-height: 100%;
    background-size: cover;
    background-position: center;
    transition: all 0.2s ease;

    &:hover {
      transform: scale(1.05);
    }

    a {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }
  }

  .preview-content {
    flex: 1;
    text-align: left;
    font-family: "Source Sans Pro", sans-serif;
    padding: 2em;

    h1 {
      margin: 0;
      padding: 0;
      line-height: 1.1;
      font-weight: bold;
      font-family: inherit;

      &:before {
        display: none;
      }
    }

    p {
      margin: 0;
      margin-top: 1.5em;
      padding: 0;
      font-family: inherit;
    }
  }
}

#external {
  width: 100%;
  height: 100%;
}

.external-page-style {
  text-align: left;
  margin-bottom: 0.9em;
}

.external-media-title {
  font-weight: 500;
  font-size: 1.75rem;

  :before {
    display: none;
  }
}
</style>
