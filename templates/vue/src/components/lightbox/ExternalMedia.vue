<template>
  <div class="external-media-container">
    <iframe
      v-if="node.behaviour === 'embed'"
      id="external"
      frameborder="0"
      allowfullscreen="allowfullscreen"
      :src="normalizedUrl"
    ></iframe>
    <div v-else class="preview">
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
</template>

<script>
import Helpers from "../../utils/Helpers"

export default {
  name: "external-media",
  props: {
    node: {
      type: Object,
      required: true,
    },
    dimensions: {
      type: Object,
      required: true,
    },
  },
  computed: {
    normalizedUrl() {
      return Helpers.normalizeUrl(this.node.typeData.mediaURL)
    },
  },
  async mounted() {
    this.$emit("complete")
  },
}
</script>

<style lang="scss" scoped>
.external-media-container {
  height: 100%;
  border-radius: 15px;
  overflow: hidden;
  background: white;
}

.preview {
  display: flex;
  height: 100%;
  width: 100%;

  .preview-image {
    cursor: pointer;
    position: relative;
    flex: 1;
    height: auto;
    background-size: cover;
    background-position: center;
    transition: all 0.2s ease;

    @media (min-width: 1000px) {
      flex: 2;
    }

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
    display: flex;
    flex-direction: column;
    justify-content: center;

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
</style>
