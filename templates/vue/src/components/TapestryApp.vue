<template>
  <div id="app-container" :class="{ 'sidebar-open': isSidebarOpen }">
    <toolbar />
    <tapestry-map
      v-if="settings.renderMap"
      :is-sidebar-open="isSidebarOpen"
      data-qa="tapestry-map"
    />
    <tapestry-main v-else ref="graph" :viewBox="viewBox" />
  </div>
</template>

<script>
import client from "../services/TapestryAPI"
import { names } from "@/config/routes"
import Toolbar from "./Toolbar"
import TapestryMain from "./TapestryMain"
import { mapMutations, mapState } from "vuex"
import TapestryMap from "./TapestryMap"
import Helpers from "@/utils/Helpers"
import { isLoggedIn } from "@/services/wp"

export default {
  components: {
    TapestryMap,
    Toolbar,
    TapestryMain,
  },
  data() {
    return {
      loading: true,
      viewBox: "2200 2700 1600 1100",
    }
  },
  computed: {
    ...mapState(["nodes", "links", "selection", "settings", "rootId", "avatar"]),
    isSidebarOpen() {
      return Boolean(this.$route.query.sidebar)
    },
    analyticsEnabled() {
      return this.settings.analyticsEnabled
    },
    hasAvatar() {
      return !(!this.avatar || Object.keys(this.avatar).length === 0)
    },
    avatarsEnabled() {
      return isLoggedIn() && process.env.VUE_APP_AVATARS === "TRUE"
    },
  },
  watch: {
    analyticsEnabled: {
      immediate: true,
      handler(analyticsEnabled) {
        client.enableAnalytics(analyticsEnabled)
      },
    },
  },
  mounted() {
    if (!this.hasAvatar && this.avatarsEnabled) {
      this.$router.push({
        name: open ? names.USERSETTINGS : names.APP,
        params: { nodeId: this.$route.params.nodeId, tab: "avatar" },
        query: this.$route.query,
      })
    }
    this.$root.$on("open-node", id => {
      this.openNode(id)
    })
    this.$root.$on("edit-node", id => {
      this.editNode(id)
    })
    client.recordAnalyticsEvent("app", "load", "tapestry")
  },
  methods: {
    ...mapMutations(["select", "unselect", "clearSelection"]),
    updateViewBox() {
      const MAX_RADIUS = 240
      const MIN_TAPESTRY_WIDTH_FACTOR = 1.5
      if (this.$refs.graph) {
        // check if TapestryMain component has rendered
        if (this.$refs.graph.$refs.app) {
          // check if <main> in TapestryMain has rendered
          const {
            width,
            height,
          } = this.$refs.graph.$refs.app.getBoundingClientRect()
          const { x0, y0, x, y } = this.getNodeDimensions()

          const tapestryDimensions = {
            startX: 0,
            startY: 0,
            width,
            height,
          }
          if (x > width || y > height) {
            tapestryDimensions.startX = x0 - MAX_RADIUS * 1.25
            tapestryDimensions.startY = y0 - MAX_RADIUS * 1.25
            tapestryDimensions.width = x
            tapestryDimensions.height = y
          }
          const windowWidth = Helpers.getBrowserWidth()
          // Center the nodes if there is not enough of them to fill the width of the screen
          if (
            tapestryDimensions.width -
              tapestryDimensions.startX -
              MAX_RADIUS * 1.25 <
            windowWidth
          ) {
            tapestryDimensions.startX -=
              (windowWidth - tapestryDimensions.width + tapestryDimensions.startX) /
                2 +
              MAX_RADIUS
          }
          tapestryDimensions.width = tapestryDimensions.width + MAX_RADIUS * 1.25
          tapestryDimensions.height = tapestryDimensions.height + MAX_RADIUS * 1.25

          const MIN_WIDTH = Helpers.getBrowserWidth() * MIN_TAPESTRY_WIDTH_FACTOR
          const MIN_HEIGHT = Helpers.getBrowserHeight() * MIN_TAPESTRY_WIDTH_FACTOR

          this.viewBox = `${tapestryDimensions.startX} ${
            tapestryDimensions.startY
          } ${Math.max(
            tapestryDimensions.width - tapestryDimensions.startX,
            MIN_WIDTH
          )} ${Math.max(
            tapestryDimensions.height - tapestryDimensions.startY,
            MIN_HEIGHT
          )}`
        }
      }
    },
    getNodeDimensions() {
      const box = {
        x0: 30000,
        y0: 30000,
        x: 0,
        y: 0,
      }
      for (const node of Object.values(this.nodes)) {
        if (node.nodeType !== "") {
          const { x, y } = node.coordinates
          box.x0 = Math.min(x, box.x0)
          box.y0 = Math.min(y, box.y0)
          box.x = Math.max(x, box.x)
          box.y = Math.max(y, box.y)
        }
      }

      return box
    },
    openNode(id) {
      this.$router.push({
        name: names.LIGHTBOX,
        params: { nodeId: id },
        query: this.$route.query,
      })
    },
    editNode(id) {
      this.$router.push({
        name: names.MODAL,
        params: { nodeId: id, type: "edit", tab: "content" },
        query: this.$route.query,
      })
    },
  },
}
</script>

<style lang="scss" scoped>
#app-container {
  position: relative;
  transform: scale(1);
  transform-origin: top left;
  transition: all 0.2s ease-out;
  width: 100%;
  z-index: 0;

  @media screen and (min-width: 500px) {
    width: calc(100% - 2.5em);

    &.sidebar-open {
      width: calc(100% - min(400px, max(300px, 25vw)) - 2.5em);
      padding-right: 0;

      .toolbar {
        padding-right: 1.5vw;
      }
    }
  }
  #tapestry {
    .empty-message {
      margin: 30vh auto;
    }
    svg {
      position: relative;
    }
  }
}
</style>

<style lang="scss">
#app {
  background-size: cover;
}
#app-container .btn-link {
  background: transparent;
}
</style>
