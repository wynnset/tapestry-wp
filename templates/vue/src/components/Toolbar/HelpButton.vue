<template>
  <button aria-label="get help" @click="open">
    <tapestry-icon icon="question-circle" />
    <help-modal :show="helpModalOpen" @close="close"></help-modal>
  </button>
</template>

<script>
import HelpModal from "@/components/modals/HelpModal"
import TapestryIcon from "@/components/common/TapestryIcon"
import { names } from "@/config/routes"
import client from "@/services/TapestryAPI"

export default {
  components: {
    HelpModal,
    TapestryIcon,
  },
  computed: {
    helpModalOpen: {
      get() {
        return this.$route.name === names.HELP
      },
      set(open) {
        this.$router.push({
          name: open ? names.HELP : names.APP,
          params: { nodeId: this.$route.params.nodeId },
          query: this.$route.query,
        })
      },
    },
  },
  methods: {
    open() {
      this.helpModalOpen = true
      client.recordAnalyticsEvent("user", "open", "help")
    },
    close() {
      this.helpModalOpen = false
      client.recordAnalyticsEvent("user", "close", "help")
    },
  },
}
</script>

<style lang="scss" scoped>
button {
  padding: 0.5rem;
  background: none;
  color: #999;
  font-size: 1.2em;
  transition: all 0.2s ease;
  outline: none;

  &.active,
  &:hover {
    background: none;
    color: #11a6d8;
    transform: scale(1.1);
  }
}
</style>
