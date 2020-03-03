<template>
  <loading v-if="loading" />
  <div v-else ref="post" class="article" @scroll="handleScroll">
    <scrollbar
      :scroll-height="scrollHeight"
      :scroll-top="scrollTop"
      :client-height="clientHeight"
      @scrollchange="handleScrollDrag"
    />
    <h1 class="article-title">{{ title }}</h1>
    <article v-html="content"></article>
  </div>
</template>

<script>
import Loading from "../Loading"
import WordpressApi from "../../services/WordpressApi"
import Scrollbar from "@/components/Scrollbar"

export default {
  name: "wp-post-media",
  components: {
    Loading,
    Scrollbar,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      title: "",
      content: "",
      scrollHeight: 0,
      scrollTop: 0,
      clientHeight: 0,
    }
  },
  computed: {
    id() {
      return this.node.typeData.mediaURL
    },
  },
  async mounted() {
    const post = await WordpressApi.getPostById(this.id)
    this.loading = false
    this.title = post.title
    this.content = post.content
    this.$emit("complete")
    this.$emit("load")

    this.$nextTick(() => {
      const post = this.$refs.post
      this.scrollHeight = post.scrollHeight
      this.clientHeight = post.clientHeight
    })
  },
  methods: {
    handleScroll() {
      this.scrollTop = this.$refs.post.scrollTop
    },
    handleScrollDrag(evt) {
      const container = this.$refs.post.getBoundingClientRect()
      const offset = (evt - container.top) / this.clientHeight
      let newValue = offset * this.$refs.post.scrollTopMax
      if (newValue < 0) {
        newValue = 0
      } else if (newValue > this.$refs.post.scrollTopMax) {
        newValue = this.$refs.post.scrollTopMax
      }
      this.scrollTop = newValue
      this.$refs.post.scrollTop = newValue
    },
  },
}
</script>

<style lang="scss" scoped>
.article {
  padding: 32px;
  text-align: left;
  height: 100%;
  overflow: scroll;
  scrollbar-width: none;
  -ms-overflow-style: none;

  &::-webkit-scrollbar {
    width: 0;
    height: 0;
  }

  &-title {
    font-size: 1.75rem;
    font-weight: bold;
    margin: 1em 0;

    :before {
      display: none;
    }
  }

  article {
    color: #47425d;
    font-family: "Source Sans Pro", sans-serif;
    font-size: 16px;
  }
}
</style>
