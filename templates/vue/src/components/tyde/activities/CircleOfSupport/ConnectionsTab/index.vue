<template>
  <div :class="['wrapper', { show: isOpen, hidden: isHidden }]">
    <cos-popup-button
      id="connections-tab-popup-trigger"
      :disabled="toolTipPositioned"
      style="left: 2rem"
      aria-label="Connections"
      @click="toggle()"
    >
      <tapestry-icon v-if="isOpen" icon="chevron-down" />
      <span v-else>😊</span>
    </cos-popup-button>
    <div
      v-if="state === states.Add || state === states.Edit"
      class="content-wrapper"
    >
      <b-overlay class="form" :show="isSubmitting">
        <add-connection-form
          v-model="connection"
          :communities="communities"
          @back="back"
          @submit="handleSubmit"
          @add-community="$emit('add-community', $event)"
        />
      </b-overlay>
    </div>
    <connections-list
      v-else
      :connections="connections"
      :communities="communities"
      :draggable="draggable"
      @add-connection="openConnectionForm"
      @edit-connection="editConnection"
      @drag:start="$emit('drag:start', $event)"
      @drag:move="$emit('drag:move', $event)"
      @drag:end="$emit('drag:end', $event)"
    />
  </div>
</template>

<script>
import TapestryIcon from "@/components/common/TapestryIcon"
import client from "@/services/TapestryAPI"
import CosPopupButton from "../CosPopupButton"
import AddConnectionForm from "./AddConnectionForm"
import ConnectionsList from "./ConnectionsList"
const States = {
  Home: 0,
  Add: 1,
  Edit: 3, // Should match editing state in the community
}
export default {
  components: {
    CosPopupButton,
    AddConnectionForm,
    ConnectionsList,
    TapestryIcon,
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
    draggable: {
      type: Boolean,
      required: false,
      default: false,
    },
    toolTipPositioned: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      isOpen: false,
      isHidden: false,
      state: States.Home,
      isSubmitting: false,
      connection: {
        id: "",
        name: "",
        avatar: "😊",
        communities: [],
      },
    }
  },
  computed: {
    states() {
      return States
    },
  },
  methods: {
    toggle() {
      // FIX : Disable attribute for community-list does not work as it is a self-made component
      if (!this.toolTipPositioned) {
        this.$emit("toggle")
        if (this.isOpen) {
          this.state = States.Home
          this.$emit("connection-closed")
        }
        this.isOpen = !this.isOpen
      }
    },
    hide() {
      if (this.isOpen) {
        this.isHidden = true
      }
    },
    show() {
      if (this.isOpen) {
        this.isHidden = false
      }
    },
    back() {
      this.state = States.Home
      this.$emit("back")
    },
    open() {
      this.isOpen = true
    },
    close() {
      this.isOpen = false
    },
    openConnectionForm() {
      this.resetConnection()
      this.state = States.Add
    },
    editConnection(connection) {
      this.open()
      this.connection = {
        ...connection,
        communities: connection.communities.map(community => community.id),
      }
      this.state = States.Edit
    },
    resetConnection() {
      this.connection = {
        id: "",
        name: "",
        avatar: "😊",
        communities: [],
      }
    },
    async handleSubmit() {
      this.isSubmitting = true
      switch (this.state) {
        case States.Add:
          await this.addNewConnection()
          this.$emit("connection-submitted")
          break
        case States.Edit:
          await this.updateConnection()
          break
        default:
          break
      }
      this.isSubmitting = false
      this.resetConnection()
      this.state = States.Home
    },
    async addNewConnection() {
      const connection = await client.cos.addConnection({
        name: this.connection.name,
        avatar: this.connection.avatar,
      })
      if (this.connection.communities.length) {
        /**
         * Add connection to community one at a time to avoid race condition where
         * only the last community is kept.
         */
        for (const communityId of this.connection.communities) {
          await client.cos.addConnectionToCommunity(communityId, connection.id)
        }
      }
      this.$emit("add-connection", {
        ...connection,
        communities: this.connection.communities,
      })
    },
    async updateConnection() {
      const currentCommunities = this.getCommunities(this.connection.id)
      await client.cos.updateConnection(this.connection.id, {
        ...this.connection,
      })
      const { additions, deletions } = this.getDifferences(
        currentCommunities.map(community => community.id),
        this.connection.communities
      )
      for (const addition of additions) {
        await client.cos.addConnectionToCommunity(addition, this.connection.id)
      }
      for (const deletion of deletions) {
        await client.cos.removeConnectionFromCommunity(deletion, this.connection.id)
      }
      this.$emit("edit-connection", {
        ...this.connection,
        additions,
        deletions,
      })
    },
    getCommunities(connectionId) {
      return Object.values(this.communities).filter(community =>
        community.connections.includes(connectionId)
      )
    },
    getDifferences(original, newVersion) {
      const additions = newVersion.filter(item => !original.includes(item))
      const deletions = original.filter(item => !newVersion.includes(item))
      return { additions, deletions }
    },
  },
}
</script>

<style lang="scss" scoped>
.wrapper {
  transform: translateY(100%);
  transition: transform 0.3s ease-out;
  min-height: 16rem;
  max-height: 80%;
  display: flex;
  flex-direction: column;
  z-index: 9;
  &.show {
    z-index: 75;
    transform: translateY(0);
  }
  &.hidden {
    transform: translateY(90%);
  }
}
ul {
  list-style: none;
  margin: 0;
  padding: 0;
}
.form {
  width: 100%;
  height: 100%;
}
.content-wrapper {
  background: white;
  position: relative;
  z-index: 10;
  height: 100%;
  border-top: 1px solid var(--cos-color-tertiary);
  flex-grow: 1;
}
</style>
