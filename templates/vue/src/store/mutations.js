import Helpers from "../utils/Helpers"

export function init(state, dataset) {
  setDataset(state, dataset)
  state.selectedNodeId = dataset.rootId
  state.tapestryIsLoaded = true
}

export function setDataset(state, dataset) {
  Object.entries(dataset).forEach(([key, value]) => {
    state[key] = value
  })
}

export function updateSettings(state, newSettings) {
  state.settings = newSettings
}

export function updateH5pSettings(state, newSettings) {
  state.h5pSettings = newSettings
}

export function updateSelectedNode(state, newNodeId) {
  state.selectedNodeId = newNodeId
}

export function updateRootNode(state, newNodeId) {
  state.rootId = newNodeId
}

// nodes
export function addNode(state, node) {
  state.nodes.push(node)
}

export function updateNode(state, payload) {
  const nodeIndex = Helpers.findNodeIndex(payload.id, state)
  const thisNode = state.nodes[nodeIndex]
  Object.entries(payload.newNode).forEach(([key, value]) => {
    thisNode[key] = value
  })
  state.nodes = [...state.nodes]
}

export function updateNodeProgress(state, payload) {
  const nodeIndex = Helpers.findNodeIndex(payload.id, state)
  state.nodes[nodeIndex].typeData.progress[0].value = payload.progress
  state.nodes[nodeIndex].typeData.progress[1].value = 1.0 - payload.progress
}

export function updateNodeCoordinates(state, payload) {
  const node = state.nodes[Helpers.findNodeIndex(payload.id, state)]
  Object.entries(payload.coordinates).forEach(([key, value]) => {
    node[key] = value
  })
}

export function fulfillNodeCondition(state, { id, condition }) {
  const node = state.nodes[Helpers.findNodeIndex(id, state)]
  const toFulfill = node.conditions.find(
    cond => cond.type === condition.type && cond.value === condition.value
  )
  if (toFulfill) {
    toFulfill.fulfilled = true
    if (node.conditions.every(cond => cond.fulfilled)) {
      node.unlocked = true
      node.accessible = true
      thisTapestryTool.reload()
    }
  }
}

// links
export function addLink(state, link) {
  state.links.push(link)
}

// quizzes
export function completeQuestion(state, { nodeId, questionId }) {
  const node = state.nodes[Helpers.findNodeIndex(nodeId, state)]
  const question = node.quiz.find(question => question.id === questionId)
  question.completed = true
}

export function updateEntry(state, { answerType, entry, nodeId, questionId }) {
  const node = state.nodes[Helpers.findNodeIndex(nodeId, state)]
  const question = node.quiz.find(question => question.id === questionId)
  const entries = question.entries || {}
  entries[answerType] = Object.values(entry)[0]
  question.entries = entries
}
