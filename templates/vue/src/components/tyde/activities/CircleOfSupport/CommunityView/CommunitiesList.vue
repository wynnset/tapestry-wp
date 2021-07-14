<template>
  <ul class="community-area" :style="{ '--cols': columns }">
    <li
      v-for="community in communitiesWithPosition"
      :key="community.id"
      :class="[
        'community-wrapper',
        community.position.orientation,
        { active: community.id === activeCommunity },
      ]"
      :style="{
        '--column-start': community.position.column,
        '--column-end': community.position.columnEnd,
        '--row': community.position.row,
      }"
    >
      <div
        :class="['community', community.position.orientation]"
        :style="{
          '--color': community.color,
        }"
      >
        <button
          :ref="`${community.id}-icon`"
          class="community-icon"
          @click="toggleCommunityTooltip(community.id)"
        >
          {{ community.icon }}
        </button>
        <button
          v-if="clickables[community.id]"
          class="toggle"
          @click="toggle(community.id)"
        >
          <tapestry-icon icon="chevron-down" />
        </button>
        <ul
          :ref="`${community.id}-list`"
          :data-orientation="community.position.orientation"
          :class="[
            'connection-list',
            community.position.orientation,
            { odd: isOdd(community.connections.length) },
          ]"
        >
          <li
            v-for="connection in community.connections"
            :key="connection.id"
            :ref="`${connection.id}-${community.id}`"
          >
            <button
              class="connection"
              @click="toggleConnectionInfo(connection.id, community.id)"
            >
              <p>{{ connection.name }}</p>
              <h1>{{ connection.avatar }}</h1>
            </button>
          </li>
        </ul>
      </div>
    </li>
    <connection-tooltip
      ref="connection-tooltip"
      :connection="activeConnection"
      :show="isTooltipVisible"
      @edit="editConnection"
      @close="activeConnectionId = null"
    />
    <div
      v-show="isCommunityTooltipVisible"
      ref="community-tooltip"
      class="community-tooltip"
    >
      <p>{{ activeCommunityTooltip.icon }}</p>
      <h1>
        {{ activeCommunityTooltip.name }}
      </h1>
      <button @click="editCommunity(activeCommunityTooltip)">
        <tapestry-icon icon="pencil-alt" />
      </button>
      <button @click="toggleCommunityTooltip(activeCommunityTooltip.id)">
        <tapestry-icon icon="times" />
      </button>
    </div>
  </ul>
</template>

<script>
import TapestryIcon from "@/components/common/TapestryIcon"
import Helpers from "@/utils/Helpers"

import ConnectionTooltip from "../ConnectionTooltip"

export default {
  components: {
    ConnectionTooltip,
    TapestryIcon,
  },
  props: {
    communities: {
      type: Object,
      required: true,
    },
    connections: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      clickables: {},
      activeCommunity: "",
      activeConnectionId: null,
      activeCommunityTooltipId: null,
    }
  },
  computed: {
    columns() {
      return Object.values(this.communities).length > 4
        ? `minmax(0, 1fr) repeat(2, var(--size)) minmax(0, 1fr)`
        : `minmax(0, 1fr) calc(var(--size) * 2) minmax(0, 1fr)`
    },
    communitiesWithPosition() {
      return Object.values(this.communities).map(
        (community, index, communities) => ({
          ...community,
          position: this.getPosition(index, communities.length),
          connections: community.connections.map(id => ({
            ...this.connections[id],
          })),
        })
      )
    },
    activeConnection() {
      if (!this.activeConnectionId) {
        return {
          name: "placeholder",
          avatar: "😊",
          communities: [
            {
              id: "placeholder",
              color: "var(--cos-color-secondary)",
            },
          ],
        }
      }
      const [connectionId] = this.activeConnectionId.split("-")
      const connection = this.connections[connectionId]
      return {
        ...connection,
        communities: Object.values(this.communities).filter(({ connections }) =>
          connections.includes(connectionId)
        ),
      }
    },
    activeCommunityTooltip() {
      return this.communities[this.activeCommunityTooltipId] || {}
    },
    isTooltipVisible() {
      return this.activeConnectionId !== null
    },
    isCommunityTooltipVisible() {
      return this.activeCommunityTooltipId !== null
    },
  },
  /**
   * Imperatively update whether the community is clickable or not. Have to do it
   * this way because we determine if a community is clickable using $refs, and
   * since Vue updates the DOM asynchronously, we can't immediately access the $refs.
   */
  mounted() {
    this.$nextTick(() => this.updateClickables())
  },
  updated() {
    this.$nextTick(() => this.updateClickables())
  },
  methods: {
    editCommunity(community) {
      this.$emit("edit-community", community)
      this.activeCommunityTooltipId = null
    },
    editConnection() {
      this.$emit("edit-connection", this.activeConnection)
      this.activeConnectionId = null
    },
    toggleCommunityTooltip(communityId) {
      if (this.activeCommunityTooltipId === communityId) {
        this.activeCommunityTooltipId = null
        return
      }
      this.activeCommunityTooltipId = communityId
      this.activeConnectionId = null

      const [communityRef] = this.$refs[`${communityId}-icon`]
      const tooltipRef = this.$refs["community-tooltip"]
      this.$nextTick(() =>
        Helpers.positionTooltip(
          communityRef,
          tooltipRef,
          document.getElementById("cos")
        )
      )
    },
    toggleConnectionInfo(connectionId, communityId) {
      const refName = `${connectionId}-${communityId}`
      if (refName === this.activeConnectionId) {
        this.activeConnectionId = null
        return
      }
      this.activeConnectionId = refName
      this.activeCommunityTooltipId = null

      const [connectionRef] = this.$refs[`${connectionId}-${communityId}`]
      const tooltipRef = this.$refs["connection-tooltip"].$el
      this.$nextTick(() =>
        Helpers.positionTooltip(
          connectionRef,
          tooltipRef,
          document.getElementById("cos")
        )
      )
    },
    toggle(communityId) {
      if (this.activeCommunity === communityId) {
        this.activeCommunity = ""
      } else if (this.isClickable(communityId)) {
        this.activeCommunity = communityId
      }
    },
    updateClickables() {
      const clickableCommunities = Object.keys(this.communities).reduce(
        (clickables, id) => {
          clickables[id] = id === this.activeCommunity || this.isClickable(id)
          return clickables
        },
        {}
      )
      if (Helpers.isDifferent(this.clickables, clickableCommunities)) {
        this.clickables = clickableCommunities
      }
    },
    // A community is only clickable if it's overflowing (i.e. there's more
    // connections than what's visible)
    isClickable(communityId) {
      // Destructure because community is part of a v-for loop
      const [communityElement] = this.$refs[`${communityId}-list`]
      if (
        ["east", "west"].some(
          orientation => communityElement.dataset.orientation === orientation
        )
      ) {
        return communityElement.scrollWidth > communityElement.clientWidth
      }
      if (
        ["north", "south"].some(
          orientation => communityElement.dataset.orientation === orientation
        )
      ) {
        return communityElement.scrollHeight > communityElement.clientHeight
      }
      return false
    },
    getPosition(index, communitySize) {
      // Map the community index to where it should go on the grid
      const positions = [
        {
          column: 2,
          row: 1,
        },
        {
          column: -2,
          row: communitySize > 7 && communitySize < 10 ? 2 : 3,
        },
        {
          column: 2,
          columnEnd: communitySize === 5 ? "span 2" : "initial",
          row: 4,
        },
        {
          column: 1,
          row: communitySize > 6 && communitySize < 9 ? 2 : 3,
        },
        {
          column: 3,
          row: 1,
        },
        {
          column: 3,
          row: 4,
        },
        {
          column: 1,
          row: communitySize > 8 ? -3 : 4,
        },
        {
          column: -2,
          row: communitySize > 9 ? -3 : 4,
        },
        {
          column: 1,
          row: 1,
        },
        {
          column: -2,
          row: 1,
        },
      ]
      const { column, columnEnd, row } = positions[index] || {}
      return {
        column,
        columnEnd,
        row,
        orientation: this.getOrientation(column, row),
      }
    },
    getOrientation(column, row) {
      if (column === 1) {
        return "east"
      }
      if (column === -2) {
        return "west"
      }
      if (row === 1) {
        return "south"
      }
      if (row === 4) {
        return "north"
      }
    },
    isOdd(number) {
      return number % 2 !== 0
    },
  },
}
</script>

<style scoped lang="scss">
ul {
  padding: 0;
  margin: 0;
  list-style: none;
}

.community-area {
  --size: clamp(10rem, 20vw, 14rem);
  display: grid;
  grid-template-columns: var(--cols);
  grid-template-rows: repeat(6, 1fr);
  grid-gap: 1rem;
  height: 100%;
  width: 100%;
}

.community-wrapper {
  --row-end: span 2;
  --border-offset: 3rem;

  grid-column-start: var(--column-start);
  grid-column-end: var(--column-end);
  grid-row-start: var(--row);
  grid-row-end: var(--row-end);
  position: relative;
  display: flex;
  justify-content: flex-end;
  pointer-events: none;

  --border-radius: 9999px;

  &.active {
    z-index: 20;
  }

  &.north {
    --row-end: span 3;
    margin-top: var(--border-offset);
    flex-direction: column;
    align-items: center;

    &.active {
      --row: 1 !important;
      --row-end: -1;

      .community {
        height: auto;
      }

      .toggle {
        transform: translateX(-50%);
      }
    }
  }

  &.east {
    justify-self: stretch;
    padding: 0;
    flex-direction: row-reverse;

    &.active {
      --column-end: -2;

      .community {
        width: auto;
      }

      .toggle {
        transform: translateY(-50%) rotate(90deg);
      }
    }
  }

  &.south {
    --row-end: span 3;
    margin-bottom: var(--border-offset);
    flex-direction: column-reverse;
    align-items: center;

    &.active {
      --row-end: -1;

      .community {
        height: auto;
      }

      .toggle {
        transform: translateX(-50%) rotate(180deg);
      }
    }
  }

  &.west {
    justify-self: stretch;

    &.active {
      --column-start: 2 !important;
      --column-end: -1;

      .community {
        width: auto;
      }

      .toggle {
        transform: translateY(-50%) rotate(-90deg);
      }
    }
  }
}

.community {
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  background: var(--color, var(--cos-color-secondary));
  position: relative;
  pointer-events: all;

  --border-radius: 9999px;

  &.north,
  &.south {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    max-width: 14rem;
  }

  &.east,
  &.west {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }

  &.north {
    border-top-left-radius: var(--border-radius);
    border-top-right-radius: var(--border-radius);
    padding-top: var(--border-offset);

    .toggle {
      top: 0;
      left: 50%;
      transform: translateX(-50%) rotate(180deg);
    }

    .community-icon {
      top: 0.5rem;
      right: 0.5rem;
    }
  }

  &.east {
    border-top-right-radius: var(--border-radius);
    border-bottom-right-radius: var(--border-radius);
    padding-right: 4rem;

    .toggle {
      top: 50%;
      right: 0;
      transform: translateY(-50%) rotate(-90deg);
    }

    .community-icon {
      top: 0.5rem;
      right: 0.5rem;
    }
  }

  &.south {
    border-bottom-left-radius: var(--border-radius);
    border-bottom-right-radius: var(--border-radius);
    padding-bottom: var(--border-offset);

    .toggle {
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
    }

    .community-icon {
      bottom: 0.5rem;
      right: 0.5rem;
    }
  }

  &.west {
    border-bottom-left-radius: var(--border-radius);
    border-top-left-radius: var(--border-radius);
    padding-left: 4rem;

    .toggle {
      top: 50%;
      left: 0;
      transform: translateY(-50%) rotate(90deg);
    }

    .community-icon {
      top: 0.5rem;
      left: 0.5rem;
    }
  }
}

.connection-list {
  width: 100%;
  height: 100%;
  overflow: hidden;

  &.north,
  &.south {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    padding-bottom: 0.5rem;
  }

  // For the horizontal communities, we're forced to use grid because flex-column
  // doesn't wrap correctly in all major browsers (yeah, I was surprised too :()
  // https://stackoverflow.com/questions/33891709/when-flexbox-items-wrap-in-column-mode-container-does-not-grow-its-width
  &.east,
  &.west {
    display: grid;
    align-items: center;
    grid-template-rows: repeat(2, 1fr);
    grid-auto-columns: min-content;
    grid-auto-flow: column;

    &.odd > :last-child {
      grid-row-end: span 2;
    }
  }

  &.east {
    // Reverse the direction of column placement so it places from right to left.
    direction: rtl;
    padding-left: 0.5rem;
  }

  &.west {
    padding-right: 0.5rem;
  }

  // Chrome doesn't allow content to overflow upwards which breaks the mechanism for
  // checking if a community is clickable or not. Currently, content only overflows
  // upwards for south-facing communities (i.e. the ones on the top of the CoS).
  //
  // As a workaround, we let these communities overflow downwards but make them
  // *look* like they're overflowing upwards by rotating the container.
  //
  // However, we still want the items to look aligned normally so we counteract
  // the rotation on each child.
  &.south {
    transform: rotate(180deg);

    > * {
      transform: rotate(180deg);
    }
  }
}

.connection {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  border-radius: 0.5rem;
  width: clamp(4rem, 8vw, 5.5rem);
  position: relative;
  padding: 0;
  margin: 0;
  background: none;
  direction: ltr;

  &:hover {
    background: rgba(255, 255, 255, 0.2);
  }

  p {
    padding: 0.25rem;
    border: 1px solid black;
    text-transform: uppercase;
    color: black;
    cursor: default;
    font-size: 0.7em;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  h1 {
    cursor: default;
  }
}

.toggle {
  position: absolute;
  background: none;
  font-size: 1.5rem;
}

.community-icon {
  position: absolute;
  font-size: 2.5rem;
  z-index: 10;
  background: none;
  padding: 0;
  margin: 0;
  color: inherit;
}

.community-tooltip {
  position: absolute;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid var(--cos-color-secondary);
  background: white;
  padding: 0.2rem 1rem;
  z-index: 20;

  p {
    margin: 0;
  }

  h1 {
    font-size: 1em;
    padding: 0;
    margin: 0;
    color: inherit;
    text-transform: uppercase;
  }

  button {
    background: none;
    padding: 0;
    color: var(--cos-color-secondary);
  }
}
</style>
