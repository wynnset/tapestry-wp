import Lightbox from "@/components/Lightbox"

const ROOT_PATH = `/nodes/:nodeId`

const parseParams = route => {
  const parsedParams = {}
  for (const key in route.params) {
    const val = route.params[key]
    parsedParams[key] = Number(val)
  }
  return parsedParams
}

const app = {
  path: ROOT_PATH,
  name: "app",
}

const lightbox = {
  path: `${ROOT_PATH}/view`,
  name: "lightbox",
  components: {
    lightbox: Lightbox,
  },
  props: {
    lightbox: parseParams,
  },
}

const multiContent = {
  path: `${ROOT_PATH}/view/:rowId`,
  name: "multiContent",
  components: {
    lightbox: Lightbox,
  },
  props: {
    lightbox: parseParams,
  },
}

const nestedMultiContent = {
  path: `${ROOT_PATH}/view/:rowId/rows/:subRowId`,
  name: "nested_multi_content",
  components: {
    lightbox: Lightbox,
  },
  props: {
    lightbox: parseParams,
  },
}

const settings = {
  path: `${ROOT_PATH}/settings/:tab`,
  name: "settings",
}

const help = {
  path: `${ROOT_PATH}/help`,
  name: "help",
}

const modal = {
  path: `${ROOT_PATH}/:type/:tab`,
  name: "modal",
}

const redirects = [
  {
    path: `${ROOT_PATH}/settings`,
    redirect: `${ROOT_PATH}/settings/appearance`,
  },
  {
    path: `${ROOT_PATH}/add`,
    redirect: `${ROOT_PATH}/add/content`,
  },
  {
    path: `${ROOT_PATH}/edit`,
    redirect: `${ROOT_PATH}/edit/content`,
  },
]

const routes = {
  app,
  multiContent,
  lightbox,
  modal,
  settings,
  nestedMultiContent,
  redirects,
  help,
}

export const names = Object.fromEntries(
  Object.entries(routes).map(([name, route]) => [name.toUpperCase(), route.name])
)

export default routes
