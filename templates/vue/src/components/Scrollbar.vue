<template>
  <div
    v-if="scrollHeight > clientHeight"
    class="scrollbar"
    :style="styles"
    @mousedown="handleDragStart"
  ></div>
</template>

<script>
export default {
  name: "scrollbar",
  props: {
    scrollTop: {
      type: Number,
      required: true,
    },
    scrollHeight: {
      type: Number,
      required: true,
    },
    clientHeight: {
      type: Number,
      required: true,
    },
  },
  computed: {
    styles() {
      const offset =
        (this.scrollTop / (this.scrollHeight - this.clientHeight)) *
        (this.clientHeight - 48)
      return {
        transform: `translateY(${offset}px)`,
      }
    },
  },
  mounted() {
    document.addEventListener("keydown", this.handleKeydown.bind(this))
  },
  beforeDestroy() {
    document.removeEventListener("keydown", this.handleKeydown.bind(this))
  },
  methods: {
    handleDragStart() {
      document.body.style.userSelect = "none"
      const handleDrag = evt => this.$emit("scrollchange", evt.clientY)
      document.addEventListener("mousemove", handleDrag)
      document.addEventListener(
        "mouseup",
        () => {
          document.removeEventListener("mousemove", handleDrag)
          document.body.style.userSelect = ""
        },
        { once: true }
      )
    },
    handleKeydown(evt) {
      const SCROLL_INCREMENT = 40
      if (this.$el.getBoundingClientRect) {
        const box = this.$el.getBoundingClientRect()
        switch (evt.which) {
          case 38: {
            // ArrowUp
            this.$emit("scrollchange", box.top - SCROLL_INCREMENT)
            break
          }
          case 40: {
            // ArrowDown
            this.$emit("scrollchange", box.bottom + SCROLL_INCREMENT)
            break
          }
          default:
            break
        }
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.scrollbar {
  position: absolute;
  top: 0;
  right: 0;
  width: 12px;
  height: 48px;
  background: gray;
  border-radius: 6px;
}
</style>
