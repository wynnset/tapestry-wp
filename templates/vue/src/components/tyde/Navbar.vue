<template>
  <div class="nav-container">
    <b-navbar tyde="light" class="navbar">
      <b-navbar-nav class="mx-auto" fill style="width:100%;">
        <b-nav-item
          v-for="tab in tabs"
          :key="tab"
          :ref="tab + '-tab'"
          active-class="active"
          link-classes="link"
          :active="selectedTab === tab"
          @click="handleTabChange(tab)"
        >
          <tyde-icon :selected="isSelectedTap(tab)" :icon="tab" />
        </b-nav-item>
      </b-navbar-nav>
    </b-navbar>
  </div>
</template>

<script>
import { mapState } from "vuex"
import TydeIcon from "./TydeIcon.vue"

export default {
  name: "navbar",
  components: {
    TydeIcon,
  },
  data() {
    return {
      selectedTab: "default",
    }
  },
  computed: {
    ...mapState(["settings"]),
    tabs() {
      return ["default", "profile", "goals", "cos"]
    },
  },
  methods: {
    handleTabChange(tab) {
      this.$emit("change-tab", tab)
      this.selectedTab = tab
    },
    isSelectedTap(tab) {
      return this.selectedTab === tab
    },
  },
}
</script>

<style lang="scss">
.nav-container {
  width: 100vw;
  z-index: 9999;
  position: fixed !important;
  top: 40px;
  left: 0;
}
.navbar {
  min-width: 250px;
  max-width: 900px;
  margin: 0 auto;
  width: 75vw;

  border-radius: 30px;
  border: solid 2px #c4e4ea;

  background-color: whitesmoke;
}
.link {
  padding: 0 !important;
}
.nav-container .active::after {
  content: "";
  display: block;
  position: relative;
  top: 10px;
  border: 2px solid #4197ac;
  margin: 0 auto;
  max-width: 55px;
}
</style>
