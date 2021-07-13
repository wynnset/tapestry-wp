<template>
  <div ref="wrapper" class="page-nav-wrapper">
    <aside
      ref="container"
      data-qa="page-nav-container"
      :class="[
        'page-nav',
        {
          lightbox: !node.fullscreen,
          fullscreen: node.fullscreen,
          closed: !opened,
        },
      ]"
      :style="{ height: node.fullscreen ? '100vh' : dimensions.height + 'px' }"
    >
      <button
        :class="[
          'page-nav-toggle',
          {
            fullscreen: node.fullscreen,
          },
        ]"
        data-qa="page-nav-toggle"
        @click="opened = !opened"
      >
        <i v-if="!opened" class="fas fa-bars fa-lg"></i>
        <i v-else class="fas fa-times fa-lg"></i>
      </button>
      <div
        :class="[
          'page-nav-content',
          {
            fullscreen: node.fullscreen,
            closed: !opened,
          },
        ]"
      >
        <div v-for="(menu, index) in menuGroups" :key="index" class="menu-wrapper">
          <b-card class="menu">
            <div>
              <b-card-text v-if="index === 0">
                {{ getMenuName(index) }}
              </b-card-text>
              <b-card-text v-else v-b-toggle="`collapse-${index}`">
                {{ getMenuName(index) }}
              </b-card-text>
            </div>
            <b-collapse
              :id="`collapse-${index}`"
              :visible="index === 0"
              class="mt-2"
            >
              <ul
                class="page-menu-item fa-ul"
                @click="changeActiveMenu(menu[0].node.id)"
              >
                <page-menu-item
                  v-for="row in menu"
                  :key="row.node.id"
                  :node="row.node"
                  :lockRows="lockRows"
                  :disabled="disabledRow(row.node)"
                  style="z-index: 10"
                  @scroll-to="scrollToRef"
                />
              </ul>
            </b-collapse>
          </b-card>
        </div>
      </div>
    </aside>
  </div>
</template>

<script>
import { mapGetters } from "vuex"
import PageMenuItem from "./PageMenuItem"
import Helpers from "@/utils/Helpers"

export default {
  name: "page-menu",
  components: {
    PageMenuItem,
  },
  props: {
    node: {
      type: Object,
      required: true,
    },
    rowRefs: {
      type: Array,
      required: true,
    },
    dimensions: {
      type: Object,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      opened: false || this.browserWidth > 800,
      browserWidth: Helpers.getBrowserWidth(),
    }
  },
  computed: {
    ...mapGetters(["getDirectChildren", "getNode", "isMultiContent"]),
    nodeId() {
      return parseInt(this.$route.params.nodeId, 10)
    },
    rows() {
      return this.node.childOrdering
        .map(id => {
          const node = this.getNode(id)
          let children = this.isMultiContent(node.id)
            ? node.childOrdering.map(this.getNode)
            : this.getDirectChildren(id).map(this.getNode)
          children = children.filter(node => !node.popup)
          return { node, children }
        })
        .filter(row => !row.node.popup)
    },
    menuGroups() {
      const menu = []
      const mainMenu = []
      this.rows.forEach(row => {
        if (row.node.typeData.isSecondaryNode) {
          let subMenu = []
          subMenu.push(row)
          menu.push(subMenu)
        } else {
          mainMenu.push(row)
        }
      })
      menu.unshift(mainMenu)
      return menu
    },
    lockRows() {
      return this.node.typeData.lockRows
    },
    disabledFrom() {
      return this.rows.findIndex(row => !row.node.completed)
    },
    rowOrder() {
      return this.getRowOrder(this.node)
    },
  },
  mounted() {
    if (this.rowRefs) {
      this.$router.push({
        ...this.$route,
        query: {
          ...this.$route.query,
          row:
            this.node.childOrdering.length > 0
              ? this.node.childOrdering[0]
              : undefined,
        },
      })
    }
    this.$emit("changeActiveMenu", this.menuGroups[0][0].node.id)
  },
  methods: {
    disabledRow(node) {
      const index = this.rows.findIndex(row => row.node.id === node.id)
      return (
        (this.lockRows && this.disabledFrom >= 0 && index > this.disabledFrom) ||
        !node.unlocked
      )
    },
    getRowOrder(node, nodes = [], visited = new Set()) {
      nodes.push(node.id)
      visited.add(node.id)
      const rows = this.isMultiContent(node.id)
        ? node.childOrdering.map(this.getNode)
        : this.getDirectChildren(node.id).map(this.getNode)
      for (const row of rows) {
        if (!visited.has(row.id)) {
          this.getRowOrder(row, nodes, visited)
        }
      }
      return nodes
    },
    scrollToRef(nodeId) {
      this.$nextTick(() => {
        if (this.rowRefs) {
          let el = this.rowRefs.find(ref => ref && ref.id === `row-${nodeId}`)
          if (el && el.hasOwnProperty("$el")) {
            el = el.$el
          }
          if (el) {
            el.scrollIntoView({
              behavior: "smooth",
            })
          }
        }
      })
    },
    getMenuName(index) {
      if (index === 0) {
        return this.node.title
      } else {
        return this.menuGroups[index][0].node.title
      }
    },
    changeActiveMenu(nodeId) {
      this.$emit("changeActiveMenu", nodeId)
    },
  },
}
</script>

<style lang="scss">
.page-nav-wrapper {
  .page-nav {
    position: relative;
    color: black;
    background: #5d656c;
    padding: 2.2rem 1.5rem;
    transform: translateY(0);
    transition: all 0.2s ease-in-out;
    font-size: 14px;
    text-align: left;
    z-index: 0;
    overflow-y: auto;
    min-width: 200px;

    &.lightbox {
      position: absolute;
      left: 0;
      top: 0;
      z-index: 11;
      border-radius: 15px 0 0 15px;

      &.closed {
        background: transparent;
        min-width: 20px;
        max-width: 20px;
      }
    }

    &.fullscreen {
      height: "100vh";
      margin: -24px 24px 0 -24px;
      z-index: 11;

      &.closed {
        @media screen and (max-width: 800px) {
          min-width: calc(16.33px + 3rem);
          width: calc(16.33px + 3rem);
        }
      }
    }

    @media screen and (min-width: 960px) {
      font-size: calc(14px + (2 * (100vw - 960px) / 1280px - 960px));
    }

    @media screen and (min-width: 1280px) {
      font-size: 16px;
    }

    .page-nav-toggle {
      background-color: transparent;
      padding: 0;
      margin-bottom: 1em;

      &.fullscreen {
        @media screen and (min-width: 801px) {
          display: none;
        }
      }
    }

    .page-nav-title {
      margin-bottom: 1em;
    }

    .page-nav-container {
      text-align: left;
    }

    .page-nav-content {
      &.fullscreen {
        &.closed {
          @media screen and (max-width: 800px) {
            display: none;
          }

          @media screen and (min-width: 801px) {
            display: block;
          }
        }
      }

      &.closed {
        display: none;
      }
    }
  }

  .menu-wrapper {
    display: flex;
    flex-direction: column;
    margin-top: 8px;
    margin-bottom: 8px;
  }

  .menu {
    z-index: 100;
    position: relative;
    float: none;
    background: white;
  }
}
</style>
