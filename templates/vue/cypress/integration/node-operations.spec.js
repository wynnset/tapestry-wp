const clickOutsideModal = () => {
  cy.get("#node-modal").click("topLeft")
}

describe("Node Operations", () => {
  beforeEach(() => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")
  })

  it("should be able to see the add, edit, and media buttons on load", () => {
    cy.getSelectedNode().then(({ id }) => {
      cy.openModal("add", id)
      cy.getByTestId("node-modal").should("be.visible")
      cy.contains(/cancel/i).click()
      cy.getByTestId("node-modal").should("not.be.visible")

      cy.openModal("edit", id)
      cy.getByTestId("node-modal").should("be.visible")
      cy.contains(/cancel/i).click()
      cy.contains(/close/i).click()
      cy.getByTestId("node-modal").should("not.be.visible")

      cy.openLightbox(id).should("be.visible")
    })
  })

  it("should show the lightbox when the node is clicked if the node doesn't have a media button", () => {
    cy.getSelectedNode().then(node => {
      cy.editNode(node.id, {
        hideMedia: true,
      })
      cy.getNodeById(node.id).click()
      cy.lightbox().should("be.visible")
    })
  })

  it("should prompt user if closing modal with changes", () => {
    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.getByTestId("node-modal").should("exist")

      cy.getByTestId("node-title").type(node.title)

      clickOutsideModal()
      cy.getByTestId("node-modal").should("exist")
      cy.get(".node-modal-confirmation").should("be.visible")

      cy.get(".node-modal-confirmation")
        .contains("Cancel")
        .click()
      cy.getByTestId("node-modal").should("be.visible")
      cy.get(".node-modal-confirmation").should("not.be.visible")

      cy.get(".close").click()
      cy.getByTestId("node-modal").should("exist")
      cy.get(".node-modal-confirmation").should("be.visible")

      cy.get(".node-modal-confirmation")
        .contains("Close")
        .click()
      cy.getByTestId("node-modal").should("not.be.visible")
      cy.get(".node-modal-confirmation").should("not.be.visible")
    })
  })

  it("should not prompt user if closing modal without changes", () => {
    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.getByTestId("node-modal").should("be.visible")

      cy.get(".close").click()
      cy.getByTestId("node-modal").should("not.be.visible")
    })

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.getByTestId("node-modal").should("be.visible")

      clickOutsideModal()
      cy.getByTestId("node-modal").should("not.be.visible")
    })
  })
})
