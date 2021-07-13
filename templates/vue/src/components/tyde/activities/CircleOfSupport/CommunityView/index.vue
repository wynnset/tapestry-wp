<template>
  <div style="height: 100%; width: 100%;">
    <communities-list
      :communities="communities"
      :connections="connections"
      @edit-connection="editConnection"
      @edit-community="editCommunity"
    />
    <connections-tab
      ref="connections"
      class="tab"
      :toolTipPositioned="toolTipPositioned"
      :connections="connections"
      :communities="communities"
      @back="handleBack"
      @add-connection="$emit('add-connection', $event)"
      @edit-connection="handleEditConnection"
      @add-community="$emit('add-community', $event)"
      @connection-submitted="$emit('connection-submitted')"
      @connection-closed="handleConnectionClosed"
    />
    <add-community-tab
      v-model="community"
      :show="isCommunityTabOpen"
      :disabled="!canAddCommunity"
      @back="state = lastState"
      @add-community="$emit('add-community', $event)"
      @toggle="toggleCommunityTab"
    />

    <onboarding
      v-if="initiateOnboarding"
      :communities="communities"
      :connections="connections"
      :parent-state="state"
      @tooltip-positioned="handleToolTipPositioned"
      @tooltip-removed="handleTooltipRemoved"
      @add-community="$emit('add-community', $event)"
    />
  </div>
</template>

<script>
import OnBoarding from "./onboarding/index.vue"
import AddCommunityTab from "./AddCommunityTab"
import ConnectionsTab from "../ConnectionsTab"
import CommunitiesList from "./CommunitiesList"
import { MAX_COMMUNITIES } from "../cos.config"
const States = {
  Home: 0,
  AddCommunity: 1,
  EditCommunity: 2,
  EditConnection: 3,
  ConnectionClosed: 4,
  AddConnection: 5,
}
export default {
  components: {
    AddCommunityTab,
    CommunitiesList,
    ConnectionsTab,
    onboarding: OnBoarding,
  },
  props: {
    connections: {
      type: Object,
      required: true,
    },
    communities: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      state: States.Home,
      lastState: States.Home,
      community: {
        name: "",
        icon: "👨‍👩‍👦",
        color: "",
      },
      toolTipPositioned: false,
      initiateOnboarding: true,
    }
  },
  computed: {
    states() {
      return States
    },
    isCommunityTabOpen() {
      return [States.EditCommunity, States.AddCommunity].includes(this.state)
    },
    canAddCommunity() {
      return Object.keys(this.communities).length < MAX_COMMUNITIES
    },
  },
  watch: {
    state(_, lastState) {
      this.lastState = lastState
    },
  },
  mounted() {
    if (this.communities.length >= 1 && this.connections.length >= 1) {
      this.initiateOnboarding = false
    }
  },
  methods: {
    toggleCommunityTab() {
      // FIX : Disable attribute for community-list does not work as it is a self-made component
      if (!this.toolTipPositioned) {
        if (this.isCommunityTabOpen) {
          this.state = States.Home
        } else {
          this.resetCommunity()
          this.state = States.AddCommunity
        }
      }
    },
    editCommunity(community) {
      this.community = community
      this.state = States.EditCommunity
    },
    resetCommunity() {
      this.community = {
        name: "",
        icon: "👨‍👩‍👦",
        color: "",
      }
    },
    editConnection(connection) {
      this.state = States.EditConnection
      this.$refs.connections.editConnection(connection)
    },
    handleBack() {
      if (this.state === States.EditConnection) {
        this.state = States.Home
        this.$refs.connections.close()
      }
    },
    handleConnectionClosed() {
      if (this.state === States.ConnectionClosed) {
        this.state === States.Home
      } else {
        this.state = States.ConnectionClosed
      }
    },
    handleEditConnection(event) {
      this.handleBack()
      this.$emit("edit-connection", event)
    },
    handleToolTipPositioned() {
      this.toolTipPositioned = true
    },
    handleTooltipRemoved() {
      this.toolTipPositioned = false
    },
  },
}
</script>

<style scoped>
* {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
}
.welcome-communities {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.tab {
  width: 100%;
  position: absolute;
  left: 0;
  bottom: 0;
}
</style>
