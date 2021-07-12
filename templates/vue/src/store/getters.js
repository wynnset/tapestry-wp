import Helpers from "@/utils/Helpers"

export function getDirectChildren(state) {
  return id => {
    const links = state.links
    return links.filter(link => link.source == id).map(link => link.target)
  }
}

export function getDirectParents(state) {
  return id => {
    return state.links.filter(link => link.target == id).map(link => link.source)
  }
}

export function getNode(state) {
  return id => state.nodes[id]
}

export function getParent(state) {
  return id => {
    const link = state.links.find(l => l.target == id || l.target.id == id)
    return link ? link.source : null
  }
}

export function isMultiContent(_, { getNode, isNestedMultiContent }) {
  return id => {
    const node = getNode(id)
    return node.mediaType === "multi-content" || isNestedMultiContent(id)
  }
}

export function isNestedMultiContent(_, { getNode, getParent }) {
  return id => {
    const parent = getParent(id)
    if (parent) {
      const parentNode = getNode(parent)
      return parentNode.mediaType === "multi-content"
    }
    return false
  }
}

export function isMultiContentRow(_, { getParent, isMultiContent }) {
  return (id, multiContent) => {
    const parent = getParent(id)
    if (!parent) {
      return false
    }
    if (multiContent !== undefined) {
      return parent === multiContent
    }
    return isMultiContent(parent)
  }
}

export function hasMultiContentAncestor(_, { getParent, isNestedMultiContent }) {
  return id => {
    const visited = new Set()
    let nodeId = id
    while (nodeId) {
      visited.add(nodeId)

      let parent = getParent(nodeId)
      if (!parent) return false
      if (isNestedMultiContent(nodeId)) return true

      if (visited.has(parent)) {
        break
      }

      nodeId = parent
    }
    return false
  }
}

export function isVisible(state, { getNode, hasMultiContentAncestor }) {
  const { showRejected } = state.settings
  return id => {
    const node = getNode(id)
    if (node.nodeType === "") {
      return false
    }
    if (!Helpers.hasPermission(node, "read", showRejected)) {
      return false
    }
    if (!Helpers.hasPermission(node, "edit", showRejected)) {
      return !hasMultiContentAncestor(node.id)
    }
    return true
  }
}

export function getQuestion(state) {
  return id => {
    const node = Object.values(state.nodes)
      .filter(node => node.typeData.activity)
      .find(node => node.typeData.activity.questions.find(q => q.id == id))
    if (node) {
      return node.typeData.activity.questions.find(q => q.id == id)
    }
    return null
  }
}

export function getAnswers(state) {
  return (nodeId, questionId) => {
    if (state?.userAnswers?.[nodeId]?.activity?.[questionId]?.answers) {
      return state.userAnswers[nodeId].activity[questionId].answers
    } else {
      return {}
    }
  }
}

export function hasPath(state) {
  return (from, to, options = {}) => {
    const { exclude = [] } = options
    const allowedLinks = state.links.filter(link => {
      return (
        exclude.find(
          blacklistedLink =>
            blacklistedLink.source === link.source &&
            blacklistedLink.target === link.target
        ) !== undefined
      )
    })

    const stack = []
    const visited = new Set()

    stack.push(from)
    visited.add(from)
    while (stack.length > 0) {
      const node = stack.pop()
      if (node === to) {
        return true
      }

      const neighbours = allowedLinks
        .filter(link => link.source == node || link.target == node)
        .map(link => (link.source == node ? link.target : link.source))
      for (const neighbour of neighbours) {
        if (!visited.has(neighbour)) {
          visited.add(neighbour)
          stack.push(neighbour)
        }
      }
    }
    return false
  }
}

export function favourites(state) {
  return state.favourites || []
}

export function isFavourite(_, { favourites }) {
  return id => favourites.findIndex(fid => fid == id) > -1
}

export function xOrFx({ settings }) {
  return settings.autoLayout ? "x" : "fx"
}

export function yOrFy({ settings }) {
  return settings.autoLayout ? "y" : "fy"
}

export function createDefaultNode({ settings }) {
  return () => Helpers.createDefaultNode({ settings })
}

export function tapestryJson(state) {
  const exportedTapestry = {
    nodes: Object.values(state.nodes).map(node => {
      const newNode = { ...node }
      return newNode
    }),
    links: state.links,
    groups: state.groups,
  }
  return exportedTapestry
}

export function getNeighbours(state) {
  return id => {
    return state.links
      .filter(link => link.source == id || link.target == id)
      .map(link => (link.source == id ? link.target : link.source))
  }
}

export function getNeighbouringLinks(state) {
  return id => {
    return state.links.filter(link => link.source == id || link.target == id)
  }
}

export function getAvatar(state) {
  return state.avatar
}
