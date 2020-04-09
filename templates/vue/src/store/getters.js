// TODO: Refactor this whole section to use the code below the marker below that says: // CODE BELOW SHOULD BE USED ABOVE
const mapIdToKey = {
  textId: "text",
  checklistId: "checklist",
  audioId: "audio",
}

export function logs(state) {
  const contents = state.nodes
    .filter(node => node.completed && node.showInBackpack)
    .map(node => ({
      type: "content",
      imageURL: node.imageURL,
      title: node.title,
      description: node.description,
      nodeId: node.id,
    }))

  const activities = []
  const nodesWithQuestions = state.nodes.filter(
    node =>
      node.quiz &&
      node.quiz.some(
        question => question.entries && Object.keys(question.entries).length > 0
      )
  )
  nodesWithQuestions.forEach(node => {
    node.quiz.forEach(question => {
      if (question.entries) {
        Object.entries(question.entries).forEach(([answerType, entry]) => {
          activities.push({
            type: "activity",
            title: question.text,
            nodeId: node.id,
            [mapIdToKey[answerType]]: getAnswer(answerType, entry),
          })
        })
      }
    })
  })

  return contents.concat(activities)
}

export function profileActivities(state) {
  let activities = []
  let nodesWithQuestions = state.nodes.filter(
    node =>
      node.quiz &&
      node.quiz.some(
        question => question.entries && Object.keys(question.entries).length > 0
      )
  )
  nodesWithQuestions.forEach(node => {
    node.quiz
      .filter(
        question =>
          question.entries &&
          state.settings.profileActivities.find(item =>
            node.quiz.find(question => item.activityRef === question.id)
          )
      )
      .forEach(question => {
        Object.entries(question.entries).forEach(([answerType, entry]) => {
          activities.push({
            id: question.id,
            title: question.text,
            nodeId: node.id,
            [mapIdToKey[answerType]]: getAnswer(answerType, entry),
          })
        })
      })
  })
  // Maintain order of the activities from state.settings.profileActivities
  let orderedActivities = []
  state.settings.profileActivities.forEach(function(key) {
    let found = false
    activities = activities.filter(function(question) {
      if (!found && question.id === key.activityRef) {
        orderedActivities.push(question)
        found = true
        return false
      } else return true
    })
  })
  return orderedActivities
}

const getAnswer = (answerType, entry) => {
  const types = {
    textId: parseText,
    checklistId: parseChecklist,
    audioId: parseAudio,
  }
  return types[answerType](entry)
}

const parseText = entry => {
  const text = entry[1]
  return text ? `<div>${text.replace(/(?:\r\n|\r|\n)/g, "<br>")}</div>` : ""
}

const parseAudio = entry => {
  return { id: entry }
}

const parseChecklist = entry => {
  const inputId = "1"
  const keys = Object.keys(entry).filter(key => key.startsWith(inputId))
  return keys.map(key => entry[key]).filter(answer => answer.length > 0)
}
// CODE BELOW SHOULD BE USED ABOVE

export function getParent(state) {
  return id => {
    const link = state.links.find(l => l.target == id || l.target.id == id)
    return link ? link.source : null
  }
}

export function getModuleContent(_, { getNode, getDirectChildren }) {
  return moduleId =>
    getDirectChildren(moduleId).map(stageId => ({
      node: getNode(stageId),
      topics: getDirectChildren(stageId)
        .map(getNode)
        .filter(content => content.completed),
    }))
}

export function getModuleActivities(_, { getNode, getDirectChildren }) {
  return moduleId => {
    const topics = getDirectChildren(moduleId).flatMap(getDirectChildren)
    return topics.flatMap(id => {
      const topic = getNode(id)
      if (topic.mediaType === "accordion") {
        // look at the rows for questions
        const rows = getDirectChildren(topic.id).map(getNode)
        return rows.filter(row => row.quiz).flatMap(getCompletedActivities)
      }
      if (topic.quiz) {
        return getCompletedActivities(topic)
      }
      return []
    })
  }
}

function getCompletedActivities(node) {
  return node.quiz.filter(activity => activity.completed)
}

export function getActivities(state) {
  return (options = {}) => {
    const { exclude = [] } = options
    return state.nodes
      .filter(node => !exclude.includes(node.id) && Boolean(node.quiz))
      .flatMap(node => node.quiz)
  }
}

export function getProfileActivities(_, { getNode, getDirectChildren }) {
  // TODO: Implement this
  // return moduleId => {
  //   const topics = getDirectChildren(moduleId).flatMap(getDirectChildren)
  //   return topics.flatMap(id => {
  //     const topic = getNode(id)
  //     if (topic.mediaType === "accordion") {
  //       // look at the rows for questions
  //       const rows = getDirectChildren(topic.id).map(getNode)
  //       return rows.filter(row => row.quiz).flatMap(getCompletedActivities)
  //     }
  //     if (topic.quiz) {
  //       return getCompletedActivities(topic)
  //     }
  //     return []
  //   })
  // }
}

export function getQuestion(state) {
  return id => {
    const node = state.nodes
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
    /* If the answer is an audio, then entry is just the audio id. */
    if (answerType === "audioId") {
      return { type: "audio", entry }
    }
    const answers = getAnswersFromEntry(entry)
    return formatEntry(answers, answerType)
  }
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
    return { type: "checklist", entry: answers.filter(answer => answer.length) }
  }
}
