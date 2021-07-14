<template>
  <div id="cos" class="cos">
    <loading v-if="isLoading" />
    <div v-else class="contents">
      <community-view
        v-if="view === views.Community"
        :connections="cos.connections"
        :communities="cos.communities"
        @add-connection="addConnection"
        @edit-connection="editConnection"
        @add-community="addCommunity"
      />
      <circle-view
        v-if="view === views.Circle"
        v-model="cos.circles"
        :connections="cos.connections"
        :communities="cos.communities"
        @add-connection="addConnection"
        @edit-connection="editConnection"
        @add-community="addCommunity"
      />
      <div class="switch">
        <button
          :class="['change-view', { active: view === views.Community }]"
          aria-label="Community view"
          @click="view = views.Community"
        >
          <div class="community-view"></div>
        </button>
        <button
          :class="['change-view', { active: view === views.Circle }]"
          aria-label="Circle view"
          @click="view = views.Circle"
        >
          <div class="circle-view"></div>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import client from "@/services/TapestryAPI"
import Loading from "@/components/common/Loading"
import CommunityView from "./CommunityView"
import CircleView from "./CircleView"

const CosView = {
  Community: 0,
  Circle: 1,
}

export default {
  components: {
    CommunityView,
    CircleView,
    Loading,
  },
  data() {
    return {
      isLoading: true,
      view: CosView.Community,
      cos: {
        circles: [],
        communities: {},
        connections: {},
      },
    }
  },
  computed: {
    views() {
      return CosView
    },
  },
  async mounted() {
    const { circles, communities, connections } = await client.cos.getActivity()

    circles.forEach(circle => this.cos.circles.push(circle))
    Object.entries(communities).forEach(([id, community]) =>
      this.$set(this.cos.communities, id, community)
    )
    Object.entries(connections).forEach(([id, connection]) =>
      this.$set(this.cos.connections, id, connection)
    )

    this.isLoading = false
  },
  methods: {
    addConnection({ communities, ...newConnection }) {
      if (communities.length) {
        communities.forEach(communityId =>
          this.addConnectionToCommunity(communityId, newConnection.id)
        )
      }
      this.$set(this.cos.connections, newConnection.id, newConnection)
    },
    addCommunity(community) {
      this.$set(this.cos.communities, community.id, community)
    },
    editConnection({ additions, deletions, id, name, avatar }) {
      additions.forEach(communityId =>
        this.addConnectionToCommunity(communityId, id)
      )
      deletions.forEach(communityId =>
        this.removeConnectionFromCommunity(communityId, id)
      )
      this.cos.connections[id] = { id, name, avatar }
    },
    addConnectionToCommunity(communityId, connectionId) {
      const community = this.cos.communities[communityId]
      community.connections.push(connectionId)
      this.cos.communities[communityId] = { ...community }
    },
    removeConnectionFromCommunity(communityId, connectionId) {
      const community = this.cos.communities[communityId]
      community.connections = community.connections.filter(id => id !== connectionId)
    },
  },
}
</script>

<style scoped lang="scss">
.cos {
  --cos-color-primary: #000;
  --cos-color-secondary: #c4c4c4;
  --cos-color-tertiary: #d8d8d8;
  --cos-border: 3px solid var(--cos-color-secondary);

  border: var(--cos-border);
  height: 80vh;
  width: 90vw;
  min-height: 250px;
  min-width: 320px;
  position: relative;
  margin: 0 auto;
  top: 55%;
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
  overflow: hidden;
}

.contents {
  height: 100%;
}

.switch {
  position: absolute;
  top: 1rem;
  left: 1rem;
  display: flex;
}

.change-view {
  width: 50%;
  background: white;
  border: 2px solid var(--cos-color-tertiary);

  --icon-size: 1.5rem;

  &:hover {
    > div {
      transform: scale(1.3);
    }
  }

  &.active {
    background: var(--cos-color-tertiary);

    > div {
      transform: scale(1.5);
    }
  }

  &:first-child {
    border-top-left-radius: 1rem;
    border-bottom-left-radius: 1rem;
  }

  &:last-child {
    border-top-right-radius: 1rem;
    border-bottom-right-radius: 1rem;
  }
}

.community-view {
  width: var(--icon-size);
  height: var(--icon-size);
  border: 2px solid var(--cos-color-tertiary);
  border-top-left-radius: 9999px;
  border-top-right-radius: 9999px;
  background: white;
}

.circle-view {
  width: var(--icon-size);
  height: var(--icon-size);
  border-radius: 50%;
  border: 2px solid var(--cos-color-tertiary);
  background: white;
}
</style>
