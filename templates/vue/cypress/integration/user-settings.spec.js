describe("User Settings", () => {
  beforeEach(() => {
    cy.fixture("three-nodes.json").as("tapestry")
    cy.setup("@tapestry")
  })

  it(`should be able to save and edit an Avatar`, () => {
    // process.env.AVATARS will always be undefined, so this test will be skipped.
    // We only want this test to run if process.env.AVATARS === "TRUE"
    if (typeof process.env.VUE_APP_AVATARS === "undefined") {
      return
    }
    cy.store()
      .its("state.avatar")
      .then(avatar => {
        if (Object.keys(avatar).length === 0) {
          cy.get(`[aria-label="Close"]`).click()
        }
        cy.getByTestId("user-settings-button").click({ force: true })
        cy.get(`[class="modal-body p-0"]`).as("scrollbar")
        cy.get("@scrollbar").scrollTo("bottom")
        cy.getByTestId("avatar-background-select").should("be.visible")
        cy.getByTestId("avatar-background-select").select("Transparent")
        cy.contains(/face/i).click()
        cy.get("@scrollbar").scrollTo("bottom")
        cy.getByTestId("avatar-eyebrow-select").should("be.visible")
        cy.getByTestId("avatar-eyebrow-select").select("UpDown")
        cy.contains(/hair/i).click()
        cy.get("@scrollbar").scrollTo(0, 500)
        cy.getByTestId("avatar-hair-select").should("be.visible")
        cy.getByTestId("avatar-hair-select").select("LongHairBigHair")
        cy.contains(/clothing/i).click()
        cy.get("@scrollbar").scrollTo("bottom")
        cy.getByTestId("avatar-glasses-select").should("be.visible")
        cy.getByTestId("avatar-glasses-select").select("Wayfarers")
        cy.get("@scrollbar").scrollTo("bottom")
        cy.contains(/save avatar/i).click()

        cy.getByTestId("user-settings-button").click({ force: true })
        cy.get("@scrollbar").scrollTo("bottom")
        cy.contains("Transparent").should("be.visible")
        cy.contains(/face/i).click()
        cy.get("@scrollbar").scrollTo("bottom")
        cy.contains("UpDown").should("be.visible")
        cy.contains(/hair/i).click()
        cy.contains("LongHairBigHair").should("be.visible")
        cy.contains(/clothing/i).click()
        cy.get("@scrollbar").scrollTo("bottom")
        cy.contains("Wayfarers").should("be.visible")
        cy.get(`[aria-label="Close"]`).click()
      })
  })
})
