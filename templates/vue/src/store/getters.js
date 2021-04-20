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

export function isMultiContent(_, { getNode, isSubMultiContent }) {
  return (id, hasAttributeFunc = () => true) => {
    const node = getNode(id)
    return (
      hasAttributeFunc(node) &&
      (node.mediaType === "multi-content" || isSubMultiContent(id, hasAttributeFunc))
    )
  }
}

export function isSubMultiContent(_, { getNode, getParent }) {
  return (id, hasParentAttributeFunc = () => true) => {
    const parent = getParent(id)
    if (parent) {
      const parentNode = getNode(parent)
      return (
        parentNode.mediaType === "multi-content" &&
        hasParentAttributeFunc(parentNode)
      )
    }
    return false
  }
}

export function isMultiContentRow(_, { getParent, isMultiContent }) {
  return (id, multiContent, hasParentAttributeFunc = () => true) => {
    const parent = getParent(id)
    if (!parent) {
      return false
    }
    if (multiContent !== undefined) {
      const parentNode = getNode(parent)
      return parent === multiContent && hasParentAttributeFunc(parentNode)
    }
    return isMultiContent(parent, hasParentAttributeFunc)
  }
}

export function hasMultiContentAncestor(_, { getParent, isSubMultiContent }) {
  return id => {
    const visited = new Set()
    let nodeId = id
    while (nodeId) {
      visited.add(nodeId)

      let parent = getParent(nodeId)
      if (!parent) return false
      if (isSubMultiContent(nodeId)) return true

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

export function getActivities(state) {
  return (options = {}) => {
    const { exclude = [] } = options
    return Object.values(state.nodes)
      .filter(node => !exclude.includes(node.id) && Boolean(node.quiz))
      .flatMap(node => node.quiz)
  }
}

export function getQuestion(state) {
  return id => {
    const node = Object.values(state.nodes)
      .filter(node => node.quiz)
      .find(node => node.quiz.find(q => q.id == id))
    if (node) {
      return node.quiz.find(q => q.id == id)
    }
    return null
  }
}

export function getEntry(_, { getQuestion }) {
  return (questionId, answerType) => {
    const question = getQuestion(questionId)
    if (!question) {
      return null
    }
    const entry = question.entries[answerType]
    if (!entry) {
      return null
    }
    /* If the answer is an audio, then entry is the audio file in base64. */
    if (answerType === "audioId") {
      return { type: "audio", entry: "data:audio/ogg; codecs=opus;base64," + entry }
    }
    const answers = getAnswersFromEntry(entry)
    return formatEntry(answers, answerType)
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

/* An answer is a value where its key is numeric */
function getAnswersFromEntry(entry) {
  return Object.entries(entry)
    .filter(obj => !isNaN(parseInt(obj[0], 10)))
    .map(i => i[1])
}

function formatEntry(answers, answerType) {
  if (answerType === "textId") {
    return {
      type: "text",
      entry: answers[0],
    }
  }
  if (answerType === "checklistId") {
    return { type: "checklist", entry: answers.filter(answer => answer !== "") }
  }
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
      if (newNode.quiz) {
        newNode.quiz = newNode.quiz.map(question => {
          return { ...question, completed: false, entries: null }
        })
      }
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
