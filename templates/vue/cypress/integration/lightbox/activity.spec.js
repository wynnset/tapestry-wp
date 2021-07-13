describe("Activity", () => {
  it("should be able to complete with a checkbox answer", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("activity")
      const question = `Select all numbers less than 3`

      cy.contains(/question text/i).click()
      cy.focused().type(question)
      cy.getByTestId("question-answer-multipleChoice-0").click({ force: true })
      cy.getByTestId("question-answer-multipleChoice-multipleAnswer").click({
        force: true,
      })

      cy.getByTestId(`choice-row-0`)
        .getByTestId(`choice-row-input-0`)
        .click()
        .type("1")
      cy.getByTestId(`choice-row-1`)
        .getByTestId(`choice-row-input-1`)
        .click()
        .type("2")
      cy.getByTestId(`add-choice-button`).click()
      cy.getByTestId(`choice-row-2`)
        .getByTestId(`choice-row-input-2`)
        .click()
        .type("3")

      cy.submitModal()
      cy.openLightbox(node.id)

      cy.route("POST", "/users/activity/**").as("submit")

      cy.lightbox().within(() => {
        cy.getByTestId(`multiple-choice-question-0`)
          .getByTestId(`multiple-choice-question-item-0-checked`)
          .click({ force: true })
        cy.getByTestId(`multiple-choice-question-1`)
          .getByTestId(`multiple-choice-question-item-1-checked`)
          .click({ force: true })

        cy.contains(/submit/i).click()
        cy.contains("Thanks!").should("be.visible")
        cy.contains(/done/i).click()
      })
      cy.lightbox().should("not.exist")
    })
  })

  it("should be able to complete with a radio answer", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("activity")
      const question = `What is 5 + 5?`

      cy.contains(/question text/i).click()
      cy.focused().type(question)
      cy.getByTestId("question-answer-multipleChoice-0").click({ force: true })
      cy.getByTestId(`choice-row-0`)
        .getByTestId(`choice-row-input-0`)
        .click()
        .type("100")
      cy.getByTestId(`choice-row-1`)
        .getByTestId(`choice-row-input-1`)
        .click()
        .type("90")
      cy.getByTestId(`add-choice-button`).click()
      cy.getByTestId(`choice-row-2`)
        .getByTestId(`choice-row-input-2`)
        .click()
        .type("10")

      cy.submitModal()
      cy.openLightbox(node.id)

      cy.route("POST", "/users/activity/**").as("submit")

      cy.lightbox().within(() => {
        cy.getByTestId(`multiple-choice-question-2`)
          .getByTestId(`multiple-choice-question-item-2-checked`)
          .click({ force: true })

        cy.contains(/submit/i).click()
        cy.contains("Thanks!").should("be.visible")
        cy.contains(/done/i).click()
      })
      cy.lightbox().should("not.exist")
    })
  })

  it("should be able to switch between questions in an activity", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("activity")
      const question = `What's your name?`
      const placeholder = "placeholder"
      const answer = "Tapestry"
      cy.contains(/add question/i).click()

      cy.contains(/question text/i).click()
      cy.focused().type(question)
      cy.getByTestId("question-answer-text-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-placeholder-0").type(placeholder)

      const question2 = `What's your favorite pet?`
      const placeholder2 = "placeholder 2"
      const answer2 = "Dog"
      cy.getByTestId("question-text-1").click()
      cy.focused().type(question2)
      cy.getByTestId("question-answer-text-1").click({ force: true })
      cy.getByTestId("question-answer-text-single-1").click({ force: true })
      cy.getByTestId("question-answer-text-single-placeholder-1").type(placeholder2)

      cy.submitModal()
      cy.openLightbox(node.id)
      cy.route("POST", "/users/activity/**").as("submit")
      cy.lightbox().within(() => {
        cy.get(`[placeholder="${placeholder}"]`).should("be.visible")
        cy.get("input").type(answer)
        cy.contains(/submit/i).click()
        cy.contains("Thanks!").should("be.visible")
        cy.getByTestId("completion-next-button").click()
        cy.get("input").type(answer2)
        cy.contains(/submit/i).click()
        cy.contains("Thanks!").should("be.visible")
        cy.contains(/done/i).click()
      })
      cy.lightbox().should("not.exist")
    })
  })

  it("should be able to edit single-line text already answered question", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("activity")
      const question = `What's your name?`
      const placeholder = "placeholder"
      const answer = "Tapestry"

      cy.contains(/question text/i).click()
      cy.focused().type(question)
      cy.getByTestId("question-answer-text-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-placeholder-0").type(placeholder)
      cy.submitModal()
      cy.openLightbox(node.id)
      cy.route("POST", "/users/activity/**").as("submit")
      cy.lightbox().within(() => {
        cy.get(`[placeholder="${placeholder}"]`).should("be.visible")
        cy.get("input").type(answer)
        cy.contains(/submit/i).click()
        cy.contains("Thanks!").should("be.visible")
        cy.contains(/done/i).click()
      })
      cy.lightbox().should("not.exist")
    })

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      const question2 = ` and what's your favorite color?`
      const answer2 = " and my favorite color is blue"
      cy.contains(/question text/i).click()
      cy.focused().type(question2)
      cy.getByTestId("question-answer-text-multi-0").click({ force: true })
      cy.submitModal()
      cy.openLightbox(node.id)
      cy.route("POST", "/users/activity/**").as("submit")
      cy.lightbox().within(() => {
        cy.get("textarea").type(answer2)
        cy.contains(/submit/i).click()
        cy.contains("Thanks!").should("be.visible")
        cy.contains(/done/i).click()
      })
      cy.lightbox().should("not.exist")
    })
  })

  it("should be able to complete an activity with a text-based answer", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("activity")
      const question = `What's your name?`
      const placeholder = "placeholder"
      const answer = "Tapestry"

      cy.contains(/question text/i).click()
      cy.focused().type(question)
      cy.getByTestId("question-answer-text-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-placeholder-0").type(placeholder)
      cy.submitModal()
      cy.openLightbox(node.id)
      cy.route("POST", "/users/activity/**").as("submit")
      cy.lightbox().within(() => {
        cy.get(`[placeholder="${placeholder}"]`).should("be.visible")
        cy.get("input").type(answer)
        cy.contains(/submit/i).click()
        cy.contains("Thanks!").should("be.visible")
        cy.contains(/done/i).click()
      })
      cy.lightbox().should("not.exist")
    })
  })

  it("should be able to complete an activity with an audio-based answer", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("activity")

      const question = `What's your name?`

      cy.contains(/question text/i).click()
      cy.focused().type(question)
      cy.getByTestId("question-answer-audio-0").click({ force: true })

      cy.submitModal()
      cy.openLightbox(node.id)

      cy.contains(/microphone access/i, { timeout: 10000 }).should("not.exist")
      cy.clock()

      cy.getByTestId("record").click()

      cy.tick(5 * 1000) // move forward by 5 seconds
      cy.contains("0:05").should("be.visible")

      cy.getByTestId("record").click() // pause
      cy.tick(5 * 1000)
      cy.contains("0:05").should("be.visible")

      cy.getByTestId("record").click() // resume

      cy.tick(3600 * 1000) // move forward by 1 hour
      cy.contains("1:00:05").should("be.visible")
      cy.getByTestId("done-button-audio").click()
      cy.getByTestId("submit-button-audio").click()
      cy.contains("Thanks!").should("be.visible")
      cy.contains(/done/i).click()
    })
  })

  it("should be able to complete with a list-based answer", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("Activity")

      const listQuestion = `Name 3 provinces of Canada.`
      const listPlaceholder = "Enter answer here"

      cy.contains(/question text/i).click()
      cy.focused().type(listQuestion)
      cy.getByTestId("question-answer-text-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-placeholder-0").type(
        listPlaceholder
      )
      cy.getByTestId("enable-list-checkbox").click({ force: true })
      cy.submitModal()
      cy.openLightbox(node.id)

      cy.route("POST", "/users/activity/**").as("submit")

      cy.lightbox().within(() => {
        cy.get(`[placeholder="${listPlaceholder}"]`).should("be.visible")
        cy.getByTestId("list-input-0").type("British Columbia")
        cy.getByTestId("list-add-0").click()
        cy.getByTestId("list-input-1").type("Alberta")
        cy.getByTestId("list-add-1").click()
        cy.getByTestId("list-input-2").type("Nova Scotia")
        cy.getByTestId("list-add-2").click()
        cy.getByTestId("list-input-3").type("Manitoba")

        cy.contains(/submit/i).click()

        cy.contains(/thanks/i).should("be.visible")
        cy.contains(/done/i).click()
      })

      cy.openLightbox(node.id)
      cy.lightbox().within(() => {
        cy.getByTestId("list-input-0").should("be.visible")
        cy.getByTestId("list-input-1").should("be.visible")
        cy.getByTestId("list-input-2").should("be.visible")
        cy.getByTestId("list-input-3").should("be.visible")
        cy.getByTestId("close-lightbox").click()
      })
      cy.lightbox().should("not.exist")
    })
  })

  it("should be able to use min max fields to control list answer type", () => {
    cy.fixture("one-node.json").as("oneNode")
    cy.setup("@oneNode")

    cy.getSelectedNode().then(node => {
      cy.openModal("edit", node.id)
      cy.changeMediaType("Activity")

      const listQuestion = `Name 10 things.`
      const listPlaceholder = "Thing:"
      const minFieldsValue = "5"
      const maxFieldsValue = "10"

      cy.contains(/question text/i).click()
      cy.focused().type(listQuestion)
      cy.getByTestId("question-answer-text-0").click({ force: true })
      cy.getByTestId("question-answer-text-single-placeholder-0").type(
        listPlaceholder
      )
      cy.getByTestId("enable-list-checkbox").click({ force: true })
      cy.getByTestId("min-list-fields-input").clear()
      cy.getByTestId("min-list-fields-input").type(minFieldsValue)
      cy.getByTestId("max-list-fields-input").clear()
      cy.getByTestId("max-list-fields-input").type(maxFieldsValue)

      cy.submitModal()
      cy.openLightbox(node.id)

      cy.route("POST", "/users/activity/**").as("submit")

      cy.lightbox().within(() => {
        cy.get(`[placeholder="${listPlaceholder}"]`).should("be.visible")
        cy.get(`[class="media-wrapper"]`).scrollTo("bottom")
        cy.getByTestId("list-add-4").click()
        cy.getByTestId("list-add-5").click()
        cy.getByTestId("list-add-6").click()
        cy.getByTestId("list-add-7").click()
        cy.getByTestId("list-add-8").click()
        cy.getByTestId("list-add-8").should("be.disabled")
        cy.getByTestId("list-input-list").each((input, index) => {
          cy.getByTestId(`list-input-${index}`).type(`Thing ${index}`)
          if (index === 9) {
            cy.contains(/submit/i).click()
            cy.contains(/thanks/i).should("be.visible")
            cy.contains(/done/i).click()
            return false
          }
        })
      })
      cy.openLightbox(node.id)
      cy.lightbox().within(() => {
        cy.get(`[class="list"]`)
          .children()
          .should("have.length", 10)
        cy.getByTestId("close-lightbox").click()
      })
      cy.lightbox().should("not.exist")
    })
  })
})
