<template>
  <li
    ref="connection"
    class="connection"
    :style="{ opacity: isDragging ? 0.2 : 1, '--size': fontSize }"
    @click="$emit('click')"
  >
    <p>{{ connection.name }}</p>
    <h1>{{ connection.avatar }}</h1>
    <ul v-if="variant !== 'name'" class="community-list">
      <li
        v-for="community in connection.communities"
        :key="community.id"
        :style="`--community-color: ${community.color}`"
      ></li>
    </ul>
  </li>
</template>

<script>
import * as d3 from "d3"

export default {
  props: {
    connection: {
      type: Object,
      required: true,
    },
    draggable: {
      type: Boolean,
      required: false,
      default: false,
    },
    size: {
      type: String,
      required: false,
      default: "base",
    },
    variant: {
      type: String,
      required: false,
      default: "base",
    },
  },
  data() {
    return {
      isDragging: false,
    }
  },
  computed: {
    fontSize() {
      const sizes = {
        sm: "0.6rem",
        base: "1rem",
      }
      return sizes[this.size]
    },
  },
  mounted() {
    if (this.draggable) {
      const connectionRef = this.$refs.connection
      d3.select(connectionRef).call(
        d3
          .drag()
          .container(document.getElementById("cos"))
          .on("start", () => {
            this.isDragging = true
            this.$emit("drag:start", {
              x: d3.event.x,
              y: d3.event.y,
              connection: this.connection,
            })
          })
          .on("drag", () => {
            this.$emit("drag:move", {
              x: d3.event.x,
              y: d3.event.y,
              connection: this.connection,
            })
          })
          .on("end", () => {
            this.isDragging = false
            this.$emit("drag:end", {
              x: d3.event.x,
              y: d3.event.y,
              connection: this.connection,
            })
          })
      )
    }
  },
}
</script>

<style lang="scss" scoped>
.connection {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  font-size: var(--size, 1rem);
  padding: 1em;
  border-radius: 0.5em;
  cursor: pointer;
  transform: translate(var(--x), var(--y));

  p {
    position: relative;
    padding: 0.25em;
    border: 1px solid currentColor;
    text-transform: uppercase;
    cursor: default;
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 100%;
    background: white;
  }

  h1 {
    font-size: 4em;
    cursor: default;
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
    height: 1em;
    width: 1em;
    border-radius: 50%;
    background-color: var(--community-color, var(--cos-color-secondary));
  }
}
</style>
