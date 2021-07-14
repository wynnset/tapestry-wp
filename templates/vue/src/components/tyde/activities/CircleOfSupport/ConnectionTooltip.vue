<template>
  <div :class="['connection-tooltip', { active: show }]">
    <div class="info">
      <p>{{ connection.name }}</p>
      <h1>
        {{ connection.avatar }}
      </h1>
      <ul class="community-list">
        <li
          v-for="community in connection.communities"
          :key="community.id"
          :style="`--community-color: ${community.color}`"
        ></li>
      </ul>
    </div>
    <div class="controls">
      <button @click="$emit('edit')">
        <tapestry-icon icon="pencil-alt" />
      </button>
      <button @click="$emit('close')">
        <tapestry-icon icon="times" />
      </button>
    </div>
  </div>
</template>

<script>
import TapestryIcon from "@/components/common/TapestryIcon"

export default {
  components: {
    TapestryIcon,
  },
  props: {
    connection: {
      type: Object,
      required: true,
    },
    show: {
      type: Boolean,
      required: true,
    },
  },
}
</script>

<style lang="scss" scoped>
.connection-tooltip {
  position: absolute;
  min-width: 10rem;
  border: var(--cos-border);
  border-radius: 1.5rem;
  padding: 1rem 0.5rem;
  display: flex;
  background: white;
  z-index: 20;
  opacity: 0;
  pointer-events: none;

  &.active {
    opacity: 1;
    pointer-events: all;
  }

  button {
    color: inherit;
    background: none;
    padding: 0;
    margin: 0;
    font-size: 1.5em;

    &:last-child {
      margin-top: 1rem;
    }
  }

  .info {
    flex: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 0.5rem;

    p {
      padding: 0.25rem;
      border: 1px solid black;
      text-transform: uppercase;
      color: black;
      cursor: default;
      font-size: 0.7em;
      margin: 0;
    }

    h1 {
      font-size: 4em;
    }
  }

  .controls {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: var(--cos-color-secondary);
  }

  .community-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    justify-items: center;
    justify-content: center;
    gap: 4px;

    li {
      height: 1rem;
      width: 1rem;
      border-radius: 50%;
      background-color: var(--community-color, var(--cos-color-secondary));
    }
  }
}
</style>
