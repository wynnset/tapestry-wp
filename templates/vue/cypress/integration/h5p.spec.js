describe("H5P", () => {
  beforeEach(() => {
    cy.fixture("one-node.json").as("oneNode")
  })

  it("Should be able to create an h5p node from existing h5p content", () => {
    // Note: This test assumes that certain H5P content has been
    // created in the system. To achieve this, either create the
    // content manually, or run the Docker application using
    // the fixtures option.
    cy.setup("@oneNode")
    const title = "Math Quiz 1"
    cy.getSelectedNode().then(root => {
      cy.getByTestId(`add-node-${root.id}`).click()
      cy.getByTestId(`node-title`).type(title)
      cy.getByTestId(`node-media-type`).select("h5p")
      cy.getByTestId(`h5p-content-select`).click()
      cy.contains("Math Quiz").click()
      cy.submitModal(30000) // long timeout since h5p submit is slow
      cy.getNodeByTitle(title).then(node => {
        cy.getNodeById(node.id).click()
        cy.openLightbox(node.id)
        // Basic visibility assertion due to iframe limitations
        // (see command). For stronger tests, we may need different
        // software.
        cy.getIFrame("h5p").should("be.visible")
      })
    })
  })
})
