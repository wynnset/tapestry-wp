<template>
  <article class="article" @scroll="handleScroll">
    <scrollbar
      :scroll-height="scrollHeight"
      :scroll-top="scrollTop"
      :client-height="clientHeight"
      @scrollchange="handleScrollDrag"
    />
    <h1>{{ node.title }}</h1>
    <div v-html="content"></div>
  </article>
</template>

<script>
import Scrollbar from "@/components/Scrollbar"

export default {
  name: "text-media",
  components: {
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
      scrollHeight: 0,
      scrollTop: 0,
      clientHeight: 0,
    }
  },
  computed: {
    content() {
      return this.node.typeData.textContent
    },
  },
  mounted() {
    this.$emit("complete")
    this.$nextTick(() => {
      this.scrollHeight = this.$el.scrollHeight
      this.clientHeight = this.$el.clientHeight
    })
  },
  methods: {
    handleScroll() {
      this.scrollTop = this.$el.scrollTop
    },
    handleScrollDrag(evt) {
      const container = this.$el.getBoundingClientRect()
      const offset = (evt - container.top) / this.clientHeight
      let newValue = offset * this.$el.scrollTopMax
      if (newValue < 0) {
        newValue = 0
      } else if (newValue > this.$el.scrollTopMax) {
        newValue = this.$el.scrollTopMax
      }
      this.scrollTop = newValue
      this.$el.scrollTop = newValue
    },
  },
}
</script>

<style lang="scss" scoped>
.article {
  height: 100%;
  padding: 0 15px;
  text-align: left;
  overflow: scroll;
  scrollbar-width: none;
  -ms-overflow-style: none;

  &::-webkit-scrollbar {
    width: 0;
    height: 0;
  }

  h1 {
    font-size: 1.75rem;
    font-weight: 500;
    margin: 1em auto;
    max-width: 700px;

    :before {
      display: none;
    }
  }

  div {
    color: #47425d;
    font-family: "Source Sans Pro", sans-serif;
    font-size: 16px;
    white-space: pre-wrap;
    max-width: 700px;
    margin: 0 auto;
  }
}
</style>

<style lang="scss">
.lightbox-text {
  padding: 0;
  margin-bottom: 1rem;
}
</style>
