<script>
export default {
  name: "headless-multi-content",
  props: {
    rows: {
      type: Array,
      required: true,
    },
    value: {
      type: null,
      required: true,
    },
    presentationStyle: {
      type: String,
      required: false,
      default: "",
    },
  },
  computed: {
    hasNext() {
      return this.rows.indexOf(this.value) < this.rows.length - 1
    },
  },
  methods: {
    isVisible(row) {
      if (this.value === row || this.presentationStyle === "slideshow") {
        return true
      } else if (this.$route.params.subRowId) {
        // To handle multiple rows
        const subRowId = this.$route.params.subRowId.toString()
        return subRowId.includes(",")
          ? subRowId.split(",").includes(row.toString())
          : row === this.$route.params.subRowId
      }
      return false
    },
    toggle(row) {
      this.$emit("input", this.isVisible(row) ? null : row)
    },
    next() {
      if (this.hasNext) {
        const currIndex = this.rows.indexOf(this.value)
        this.toggle(this.rows[currIndex + 1])
      }
    },
  },
  render() {
    return this.$scopedSlots.default({
      isVisible: this.isVisible,
      hasNext: this.hasNext,
      next: this.next,
      toggle: this.toggle,
    })
  },
}
</script>
