<template>
  <article :class="{ article: true, 'page-style': context == 'page' }">
    <h1 v-if="showTitle">{{ node.title }}</h1>
    <div v-html="content"></div>
  </article>
</template>

<script>
export default {
  name: "text-media",
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
  },
  computed: {
    content() {
      return this.node.typeData.textContent
    },
    showTitle() {
      return (
        this.context !== "page" ||
        (this.context === "page" && this.node.typeData.showTitle !== false)
      )
    },
  },
  mounted() {
    this.$emit("complete")
    this.$emit("load")
  },
}
</script>

<style lang="scss" scoped>
.article {
  padding: 1em;
  text-align: left;
  min-height: 100%;
  background: rgb(238, 238, 238);

  h1 {
    font-size: 1.75rem;
    font-weight: 500;
    margin-bottom: 1em;

    :before {
      display: none;
    }
  }

  div {
    font-family: "Source Sans Pro", sans-serif;
    font-size: 16px;
    white-space: pre-wrap;
    margin-bottom: 0.9em;
  }
}

.page-style {
  padding: 0 !important;
  background: #262626 !important;

  > h1 {
    margin-top: 0 !important;
    margin-bottom: 0.9em;
  }
}
</style>
