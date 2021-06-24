import "cypress-file-upload"
import "@testing-library/cypress/add-commands"
import roles from "./roles"
import { deepMerge } from "./utils"
import { API_URL, TEST_TAPESTRY_NAME } from "./constants"

Cypress.Commands.add("setup", { prevSubject: false }, (fixture, role = "admin") => {
  if (fixture) {
    cy.get(fixture).then(tapestry => {
      cy.addTapestry(tapestry)
    })
  } else {
    cy.addTapestry()
  }
  if (role !== "public") {
    cy.login(role)
  }
  cy.visitTapestry()
})

Cypress.Commands.add("login", role => {
  const { username, password } = roles[role]
  cy.request("POST", `${API_URL}/login`, { username, password })
})

Cypress.Commands.add("logout", () => cy.request(`${API_URL}/logout`))

Cypress.Commands.add("addTapestry", (body = {}) => {
  return cy.request({
    url: `${API_URL}/tapestries`,
    body: { title: TEST_TAPESTRY_NAME, ...body },
    method: "POST",
  })
})

Cypress.Commands.add("deleteTapestry", (title = TEST_TAPESTRY_NAME) => {
  return cy.request({
    url: `${API_URL}/tapestries`,
    body: { title },
    method: "DELETE",
  })
})

Cypress.Commands.add("visitTapestry", () => {
  cy.visit(`/tapestry/${TEST_TAPESTRY_NAME}`)
  cy.getByTestId("tapestry-loading").should("not.exist")
})

// -- Nodes --

Cypress.Commands.add("getNodeByTitle", title =>
  cy.findNode(node => node.title === title)
)

Cypress.Commands.add("getSelectedNode", () =>
  cy
    .store()
    .its("state")
    .then(({ nodes, selectedNodeId }) => nodes[selectedNodeId])
)

Cypress.Commands.add("addNode", { prevSubject: false }, (parent, node) => {
  cy.server()
  cy.route("POST", `**/nodes`).as("addNode")
  cy.store().then(store => {
    store.dispatch("addNode", deepMerge(store.getters.createDefaultNode(), node))
    cy.wait("@addNode")
    cy.getNodeByTitle(node.title).then(({ id }) => {
      store.commit("updateVisibleNodes", [...store.state.visibleNodes, id])
      if (parent) {
        cy.addLink(parent.id || parent, id)
      }
    })
  })
})

Cypress.Commands.add("editNode", { prevSubject: false }, (id, newNode) => {
  cy.server()
  cy.route("PUT", `**/nodes/**`).as("editNode")

  cy.store().then(store => store.dispatch("updateNode", { id, newNode }))

  cy.wait("@editNode")
})

Cypress.Commands.add(
  "updateNodeProgress",
  { prevSubject: false },
  (id, progress) => {
    cy.server()
    cy.route("POST", `**/users/progress`).as("saveProgress")

    cy.store().then(store => store.dispatch("updateNodeProgress", { id, progress }))

    cy.store()
      .its("state.nodes")
      .should(nodes => {
        const node = nodes[id]
        expect(node.progress).to.equal(progress)
      })
  }
)

Cypress.Commands.add("deleteNode", () => {
  cy.server()
  cy.route("DELETE", `**/nodes/**`).as("deleteNode")

  cy.contains(/delete/i).click()
  cy.wait("@deleteNode")
})

Cypress.Commands.add("findNode", pred => {
  return cy
    .store()
    .its("state.nodes")
    .then(nodes => Object.values(nodes).find(pred) || null)
})

Cypress.Commands.add("getNodeById", id => cy.getByTestId(`node-${id}`))

// -- Lightbox --

Cypress.Commands.add("lightbox", () => cy.getByTestId("lightbox"))

Cypress.Commands.add("openLightbox", { prevSubject: "optional" }, (node, id) => {
  cy.getByTestId(`open-node-${id || node.id}`).click()
  return cy.lightbox()
})

Cypress.Commands.add("closeLightbox", () => cy.getByTestId("close-lightbox").click())

// -- Links --

Cypress.Commands.add("link", (source, target) =>
  cy.getByTestId(`link-${source}-${target}`)
)

Cypress.Commands.add("addLink", (source, target) => {
  cy.server()
  cy.route("POST", "**/links").as("postLink")
  cy.store()
    .its("dispatch")
    .then(dispatch => dispatch("addLink", { source, target }))
  cy.wait("@postLink")
})

// -- Modal --

Cypress.Commands.add("openModal", (type, id) => {
  switch (type) {
    case "add":
      return cy.getByTestId(`add-node-${id}`).click()
    case "edit":
      return cy.getByTestId(`edit-node-${id}`).click()
    case "settings":
      return cy.getByTestId("settings-button").click()
    default:
      throw new Error(`Unknown modal type: ${type}`)
  }
})

Cypress.Commands.add("submitModal", (timeout = 10000) => {
  cy.server()
  cy.route("PUT", `**/nodes/**/permissions`).as("editPermissions")

  cy.getByTestId("submit-node-modal").click()
  cy.getByTestId("node-modal", { timeout: timeout }).should("not.be.visible")
})

Cypress.Commands.add("submitSettingsModal", () => {
  cy.server()
  cy.route("PUT", `**/settings`).as("save")

  cy.getByTestId("settings-modal").within(() => {
    cy.getByTestId("submit-button").click()
  })
  cy.wait("@save")
})

Cypress.Commands.add("changeMediaType", type =>
  cy.getByTestId(`node-media-type`).select(type)
)

// -- Utils --

Cypress.Commands.add("sidebar", () => cy.getByTestId("sidebar"))

Cypress.Commands.add("app", () => cy.window().its("app"))

Cypress.Commands.add("store", () => cy.window().its("app.$store"))

Cypress.Commands.add("getByTestId", (testId, ...args) =>
  cy.get(`[data-qa="${testId}"]`, ...args)
)

Cypress.Commands.add("getEditable", testId =>
  cy.getByTestId(testId).find("[contenteditable=true]")
)

// Get an iFrame subject by id and execute optional
// assertions on its document
// Limitation: cannot do cypress commands on subjects within the frame,
// only make assertions on the static DOM object
Cypress.Commands.add("getIFrame", (id, fn = fr => fr) =>
  cy
    .get(`iframe[id="${id}"]`)
    // Assertions like this will fail
    // .find("body")
    // .should("exist")
    // However,
    .should(frame => {
      // this assertion passes
      // (reference: https://github.com/danteoh/cypress-iframe-test/blob/master/cypress/integration/iframe_spec.js)
      expect(frame.contents().find("body")).to.exist

      // and we can do arbitrary assertions on the contents
      fn(frame.contents())
    })
)
