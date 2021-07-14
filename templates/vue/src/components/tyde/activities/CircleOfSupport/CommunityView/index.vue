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
      :connections="connections"
      :communities="communities"
      @back="handleBack"
      @add-connection="$emit('add-connection', $event)"
      @edit-connection="handleEditConnection"
      @add-community="$emit('add-community', $event)"
    />
    <add-community-tab
      v-model="community"
      :show="isCommunityTabOpen"
      :disabled="!canAddCommunity"
      @back="state = lastState"
      @add-community="$emit('add-community', $event)"
      @toggle="toggleCommunityTab"
    />
  </div>
</template>

<script>
import AddCommunityTab from "./AddCommunityTab"
import ConnectionsTab from "../ConnectionsTab"
import CommunitiesList from "./CommunitiesList"
import { MAX_COMMUNITIES } from "../cos.config"

const States = {
  Home: 0,
  AddCommunity: 1,
  EditCommunity: 2,
  EditConnection: 3,
}

export default {
  components: {
    AddCommunityTab,
    CommunitiesList,
    ConnectionsTab,
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
  methods: {
    toggleCommunityTab() {
      if (this.isCommunityTabOpen) {
        this.state = States.Home
      } else {
        this.resetCommunity()
        this.state = States.AddCommunity
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
    handleEditConnection(event) {
      this.handleBack()
      this.$emit("edit-connection", event)
    },
  },
}
</script>

<style scoped>
.tab {
  width: 100%;
  position: absolute;
  left: 0;
  bottom: 0;
}
</style>
