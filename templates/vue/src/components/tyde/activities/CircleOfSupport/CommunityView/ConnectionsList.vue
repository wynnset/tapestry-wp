<template>
  <div class="content-wrapper">
    <div class="controls">
      <div class="search">
        <button class="content-control" aria-label="search" @click="toggleSearch">
          <tapestry-icon icon="search" />
        </button>
        <div v-if="searching" class="searchbar">
          <label id="search-label" style="display: none;">
            Search for a connection
          </label>
          <input
            ref="connectionSearch"
            v-model="search"
            aria-labelledby="search-label"
            type="text"
          />
        </div>
      </div>
      <button
        class="content-control"
        aria-label="add connection"
        @click="$emit('add-connection')"
      >
        <tapestry-icon icon="plus" />
      </button>
    </div>
    <ul :class="['connection-list', { searching }]">
      <li
        v-for="visibleConnection in visibleConnections"
        :key="visibleConnection.id"
        class="connection"
        @click="$emit('edit-connection', visibleConnection)"
      >
        <p>{{ visibleConnection.name }}</p>
        <h1>{{ visibleConnection.avatar }}</h1>
        <ul class="community-list">
          <li
            v-for="community in visibleConnection.communities"
            :key="community.id"
            :style="`--community-color: ${community.color}`"
          ></li>
        </ul>
      </li>
    </ul>
  </div>
</template>

<script>
import { matchSorter } from "match-sorter"
import TapestryIcon from "@/components/common/TapestryIcon"

export default {
  components: {
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
  },
  data() {
    return {
      searching: false,
      search: "",
    }
  },
  computed: {
    visibleConnections() {
      const populated = Object.values(this.connections).map(connection => ({
        ...connection,
        communities: this.getCommunities(connection.id),
      }))
      const matches = matchSorter(populated, this.search, { keys: ["name"] })
      return matches
    },
  },
  methods: {
    toggleSearch() {
      this.searching = !this.searching
      if (this.searching) {
        this.$nextTick(() => this.$refs.connectionSearch.focus())
      }
    },
    getCommunities(connectionId) {
      return Object.values(this.communities).filter(community =>
        community.connections.includes(connectionId)
      )
    },
  },
}
</script>

<style scoped lang="scss">
.search {
  display: flex;
}

.content-wrapper {
  background: white;
  position: relative;
  z-index: 10;
  height: 100%;
  border-top: 1px solid var(--cos-color-tertiary);
  flex-grow: 1;
  overflow: hidden;
}

.controls {
  position: absolute;
  left: 2rem;
  top: 2rem;
}

.content-control {
  width: 5rem;
  height: 5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--cos-color-secondary);
  margin: 0;
  padding: 0;
  border-radius: 1rem;
  font-size: 3rem;
  background: white;

  &:hover {
    background: var(--cos-color-tertiary);
    color: white;
  }
}

.connection-list {
  width: calc(100% - 7rem);
  overflow-y: scroll;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(8rem, 1fr));
  grid-auto-rows: min-content;
  margin-left: 7rem;
  height: 100%;
  padding: 1rem;

  &.searching {
    margin-top: 7rem;
    height: calc(100% - 7rem);
  }
}

.searchbar {
  display: flex;
  align-items: center;
  margin-left: 1rem;

  input {
    border-radius: 0.5rem;
    width: 100%;
    height: 5rem;
    font-size: 2rem;
  }
}

.connection {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  min-height: 12rem;
  border-radius: 0.5rem;
  cursor: pointer;

  p {
    padding: 0.25rem;
    border: 1px solid var(--cos-color-tertiary);
    color: var(--cos-color-secondary);
    text-transform: uppercase;
    cursor: default;
    margin-bottom: 2rem;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  h1 {
    font-size: 4rem;
    cursor: default;
    margin-bottom: 2rem;
  }

  &:hover {
    background: #f0f0f0;
  }
}

.community-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px;

  li {
    height: 1rem;
    width: 1rem;
    border-radius: 50%;
    background-color: var(--community-color, var(--cos-color-secondary));
  }
}
</style>
