<template>
  <b-modal
    id="help-modal"
    data-qa="help-modal"
    :visible="show"
    :hide-footer="true"
    size="xl"
    title="Authoring Guide"
    body-class="p-0"
    @hidden="$emit('close')"
  >
    <b-container fluid class="px-0">
      <loading v-if="loading" style="height: 400px;"></loading>
      <iframe
        v-show="!loading"
        height="400px"
        width="100%"
        src="https://tapestry-tool.com/guide/"
        @load="handleLoad()"
      ></iframe>
    </b-container>
  </b-modal>
</template>

<script>
import Loading from "@/components/common/Loading"

// This determines how long the loading icon shows before the iframe is revealed.
// Note: The loading icon will automatically disappear if the iframe is fully
// loaded before this time is reached.
const loadingLimit = 5000

export default {
  name: "help-modal",
  components: {
    Loading,
  },
  props: {
    show: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
    }
  },
  mounted() {
    this.$root.$on("bv::modal::show", (_, modalId) => {
      if (modalId === "help-modal") {
        setTimeout(() => {
          this.loading = false
        }, loadingLimit)
      }
    })
    this.$root.$on("bv::modal::hide", (_, modalId) => {
      if (modalId === "help-modal") {
        this.loading = true
      }
    })
  },
  methods: {
    closeModal() {
      this.$emit("close")
    },
    handleLoad() {
      this.loading = false
    },
  },
}
</script>

<style>
iframe {
  border: none;
  min-height: 70vh;
}
</style>
