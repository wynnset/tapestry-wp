import Vue from "vue"
import VueRouter from "vue-router"

import routeConfig, { names } from "./config/routes"
import store from "./store"

Vue.use(VueRouter)

export const routes = [
  routeConfig.app,
  routeConfig.lightbox,
  routeConfig.multiContent,
  routeConfig.nestedMultiContent,
  routeConfig.settings,
  routeConfig.userSettings,
  ...routeConfig.redirects,
  routeConfig.modal,
]

const router = new VueRouter({
  routes,
})

// sourced from https://stackoverflow.com/questions/58634914
const originalPush = VueRouter.prototype.push
VueRouter.prototype.push = function push(location) {
  return originalPush.call(this, location).catch(err => {
    if (err.name !== "NavigationDuplicated") throw err
  })
}

router.beforeEach((to, from, next) => {
  const nodes = Object.keys(store.state.nodes)
  if (to.matched.length === 0 && nodes.length > 0) {
    next({ name: names.APP, params: { nodeId: store.state.rootId } })
  } else {
    next()
  }
})

export default router
