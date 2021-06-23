import client from "../services/TapestryAPI"
import * as wp from "../services/wp"
import Helpers from "../utils/Helpers"
import ErrorHelper from "../utils/errorHelper"

const LOCAL_PROGRESS_ID = "tapestry-progress"

export async function updateSettings({ commit, dispatch }, newSettings) {
  try {
    await client.updateSettings(JSON.stringify(newSettings))
    commit("updateSettings", newSettings)
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function updateH5pSettings({ commit, dispatch }, newSettings) {
  try {
    await client.updateH5pSettings(newSettings)
    commit("updateH5pSettings", newSettings)
  } catch (error) {
    dispatch("addApiError", error)
  }
}

// nodes
export async function addNode({ commit, dispatch, getters, state }, newNode) {
  try {
    const response = await client.addNode(JSON.stringify(newNode))
    const nodeToAdd = { ...newNode }
    const id = response.data.id
    nodeToAdd.id = id
    nodeToAdd.author = response.data.author
    nodeToAdd.permissions = response.data.permissions
    if (response.data.typeData.h5pMeta) {
      nodeToAdd.typeData.h5pMeta = response.data.typeData.h5pMeta
    }

    commit("addNode", nodeToAdd)
    commit("updateVisibleNodes", [...state.visibleNodes, id])
    commit("updateNodeCoordinates", {
      id,
      coordinates: {
        [getters.xOrFx]: nodeToAdd.coordinates.x,
        [getters.yOrFy]: nodeToAdd.coordinates.y,
      },
    })
    return id
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function updateNode({ commit, dispatch, getters }, payload) {
  try {
    const response = await client.updateNode(
      payload.id,
      JSON.stringify(payload.newNode)
    )

    const newNode = { ...payload.newNode }
    newNode.id = response.data.id
    const id = payload.id
    commit("updateNode", {
      id,
      newNode: newNode,
    })
    if (newNode.coordinates) {
      commit("updateNodeCoordinates", {
        id,
        coordinates: {
          [getters.xOrFx]: newNode.coordinates.x,
          [getters.yOrFy]: newNode.coordinates.y,
        },
      })
    }
    return id
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function updateLockedStatus({ commit, getters, dispatch }) {
  try {
    const userProgress = await client.getUserProgress()
    for (const [nodeId, progress] of Object.entries(userProgress)) {
      const node = getters.getNode(nodeId)
      if (node) {
        const { accessible, unlocked } = progress
        if (
          Helpers.isDifferent(
            {
              accessible: node.accessible,
              unlocked: node.unlocked,
            },
            { accessible, unlocked }
          )
        ) {
          commit("updateNode", { id: nodeId, newNode: { accessible, unlocked } })
        }
      }
    }
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function updateNodeProgress({ commit, dispatch }, payload) {
  try {
    const { id, progress } = payload

    if (!wp.isLoggedIn()) {
      const progressObj = JSON.parse(localStorage.getItem(LOCAL_PROGRESS_ID))
      const nodeProgress = progressObj[id] || {}
      nodeProgress.progress = progress
      localStorage.setItem(LOCAL_PROGRESS_ID, JSON.stringify(progressObj))
    } else {
      await client.updateUserProgress(id, progress)
    }
    commit("updateNodeProgress", { id, progress })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function updateNodeCoordinates(
  { commit, dispatch },
  { id, coordinates, originalCoordinates }
) {
  try {
    await client.updateNodeCoordinates(id, coordinates)
    commit("updateNodeCoordinates", { id, coordinates })
  } catch (error) {
    if (originalCoordinates) {
      commit("updateNodeCoordinates", { id, coordinates: originalCoordinates })
    }
    dispatch("addApiError", error)
    return Promise.reject()
  }
}

export async function completeNode(context, nodeId) {
  const { commit, dispatch, getters } = context
  try {
    if (!wp.isLoggedIn()) {
      const progressObj = JSON.parse(localStorage.getItem(LOCAL_PROGRESS_ID))
      const nodeProgress = progressObj[nodeId] || {}
      nodeProgress.completed = true
      localStorage.setItem(LOCAL_PROGRESS_ID, JSON.stringify(progressObj))
    } else {
      await client.completeNode(nodeId)
    }
    commit("updateNode", {
      id: nodeId,
      newNode: { completed: true },
    })

    const node = getters.getNode(nodeId)
    if (node.mediaType !== "video") {
      await dispatch("updateNodeProgress", {
        id: nodeId,
        progress: 1,
      })
    }
    return unlockNodes(context)
  } catch (error) {
    dispatch("addApiError", error)
  }
}

async function unlockNodes({ commit, getters, dispatch }) {
  try {
    const progress = await client.getUserProgress()
    for (const [id, nodeProgress] of Object.entries(progress)) {
      const currentNode = getters.getNode(id)
      if (
        currentNode &&
        Helpers.isDifferent(
          {
            accessible: nodeProgress.accessible,
            unlocked: nodeProgress.unlocked,
          },
          { accessible: currentNode.accessible, unlocked: currentNode.unlocked }
        )
      ) {
        const { accessible, unlocked, content, conditions } = nodeProgress
        const newNode = { accessible, unlocked, conditions }
        if (accessible) {
          const { quiz, typeData } = content
          newNode.quiz = quiz
          newNode.typeData = typeData
        }
        commit("updateNode", { id, newNode })
      }
    }
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function deleteNode({ commit, dispatch }, id) {
  try {
    await client.deleteNode(id)
    commit("deleteNode", id)
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function getNodeHasDraftChildren({ dispatch }, id) {
  try {
    return await client.getNodeHasDraftChildren(id)
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function getTapestryExport({ dispatch }) {
  try {
    return await client.getTapestryExport()
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function completeQuestion(
  { commit, dispatch },
  { answerType, formId, nodeId, questionId }
) {
  try {
    await client.completeQuestion(nodeId, questionId)
    if (answerType !== "audioId") {
      const entry = await client.getUserEntry(formId)
      commit("updateEntry", { answerType, entry, nodeId, questionId })
    }
    commit("completeQuestion", { nodeId, questionId })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function saveAudio(
  { commit, dispatch },
  { audio, nodeId, questionId }
) {
  try {
    await client.saveAudio(audio, nodeId, questionId)
    commit("updateEntry", {
      answerType: "audioId",
      entry: { audio },
      nodeId,
      questionId,
    })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function reviewNode({ commit, dispatch }, { id, comments }) {
  try {
    const updates = await client.reviewNode(id, comments)
    commit("updateNode", {
      id,
      newNode: updates.data,
    })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

// links
export async function addLink({ commit, dispatch, getters }, newLink) {
  try {
    await client.addLink(JSON.stringify(newLink))
    commit("addLink", newLink)

    const parent = getters.getNode(newLink.source)
    commit("updateNode", {
      id: newLink.source,
      newNode: {
        childOrdering: [...parent.childOrdering, newLink.target],
      },
    })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function deleteLink(
  { commit, dispatch },
  { source, target, useClient = true }
) {
  try {
    if (useClient) await client.deleteLink({ source: source, target: target })
    commit("deleteLink", { source, target })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

// favourites
export function toggleFavourite({ dispatch, getters }, id) {
  const favourites = getters.favourites
  const newFavourites = getters.isFavourite(id)
    ? favourites.filter(fid => fid != id)
    : [...favourites, id]
  dispatch("updateUserFavourites", newFavourites)
}

export async function updateUserFavourites({ commit, dispatch }, favourites) {
  try {
    await client.updateUserFavourites(JSON.stringify(favourites))
    commit("updateFavourites", { favourites })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export async function refetchTapestryData(
  { commit, state, dispatch },
  filterUserId = null
) {
  try {
    const query = filterUserId === null ? {} : { filterUserId: filterUserId }
    const tapestry = await client.getTapestry(query)
    const { nodes, links } = state

    const nodeIds = new Set(Object.values(nodes).map(node => node.id))
    const newNodeIds = new Set(tapestry.nodes.map(node => node.id))

    const nodeDiff = {
      additions: tapestry.nodes.filter(node => !nodeIds.has(node.id)),
      deletions: Object.values(nodes).filter(node => !newNodeIds.has(node.id)),
    }

    const getLinkId = link => `${link.source}-${link.target}`
    const currentLinks = new Set(links.map(getLinkId))
    const newLinks = new Set(tapestry.links.map(getLinkId))

    const linkDiff = {
      additions: tapestry.links.filter(link => !currentLinks.has(getLinkId(link))),
      deletions: links.filter(link => !newLinks.has(getLinkId(link))),
    }

    commit("updateDataset", { nodes: nodeDiff, links: linkDiff })
  } catch (error) {
    dispatch("addApiError", error)
  }
}

export function addApiError({ commit }, error) {
  const message = ErrorHelper.getErrorMessage(error)
  commit("addApiError", { error: message })
}

export function setTapestryErrorReporting({ commit }, isEnabled) {
  commit("setTapestryErrorReporting", isEnabled)
}
