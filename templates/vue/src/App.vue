<template>
  <loading v-if="loading" data-qa="tapestry-loading" style="height: 75vh;"></loading>
  <div v-else id="app">
    <tapestry-app></tapestry-app>
    <router-view name="lightbox"></router-view>
    <node-modal></node-modal>
    <sidebar v-if="!isEmpty"></sidebar>
    <tapestry-error></tapestry-error>
    <b-modal
      id="loggedOutModal"
      :visible="!loggedIn"
      title="Not Logged In"
      no-close-on-backdrop
    >
      You can either refresh and stay logged out or log in again.
      <template #modal-footer>
        <b-button @click="refresh">Refresh</b-button>
        <b-button @click="redirectToLogin">Log In</b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex"
import NodeModal from "@/components/modals/NodeModal"
import TapestryApp from "@/components/TapestryApp"
import Sidebar from "@/components/Sidebar"
import TapestryError from "@/components/TapestryError"
import Loading from "@/components/common/Loading"
import client from "@/services/TapestryAPI"
import { isLoggedIn } from "./services/wp"

export default {
  name: "app",
  components: {
    Loading,
    NodeModal,
    TapestryApp,
    Sidebar,
    TapestryError,
  },
  data() {
    return {
      loading: true,
      loggedIn: true,
    }
  },
  computed: {
    ...mapState(["nodes"]),
    isEmpty() {
      return Object.keys(this.nodes).length === 0
    },
  },
  watch: {
    loggedIn(isStillLoggedIn) {
      if (!isStillLoggedIn) {
        this.$bvModal.show("loggedOutModal")
      }
    },
  },
  mounted() {
    if (isLoggedIn()) {
      var that = this
      jQuery(function($) {
        wp.heartbeat.interval("fast")

        $(document).on("heartbeat-tick", function(event, data) {
          that.loggedIn = data["wp-auth-check"]
        })
      })
      Promise.resolve(client.getAvatar()).then(savedAvatar => {
        this.addAvatar(savedAvatar.data)
      })
    }
    window.addEventListener("click", this.recordAnalytics)
    const data = [client.getTapestry(), client.getUserProgress()]
    Promise.all(data).then(([dataset, progress]) => {
      this.init({ dataset, progress })
      this.loading = false
      if (!this.$route.params.nodeId && dataset.nodes.length > 0) {
        this.$router.replace({
          path: `/nodes/${dataset.rootId}`,
          query: this.$route.query,
        })
      }
    })
  },
  beforeDestroy() {
    window.removeEventListener("click", this.recordAnalytics)
  },
  methods: {
    ...mapMutations(["init", "addAvatar"]),
    refresh() {
      this.$router.go()
    },
    redirectToLogin() {
      window.location.href = `${window.location.origin}/wp-login.php?redirect_to=${window.location.href}`
    },
    recordAnalytics(evt) {
      const x = evt.clientX + window.pageXOffset
      const y = evt.clientY + window.pageYOffset
      client.recordAnalyticsEvent("user", "click", "screen", null, {
        x: x,
        y: y,
      })
    },
  },
}
</script>

<style lang="scss">
html {
  font-size: 100%;

  #app {
    font-family: "Avenir", Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;

    li {
      line-height: initial;
    }

    h1,
    h2,
    h3,
    h4,
    h5 {
      &::before {
        display: none;
      }
    }

    button:focus {
      outline: none;
    }

    .btn {
      &:disabled,
      &.disabled {
        * {
          opacity: 50%;
        }
      }
    }
  }
}
</style>
