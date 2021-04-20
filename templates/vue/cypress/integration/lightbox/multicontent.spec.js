describe("Multi-content", () => {
  beforeEach(() => {
    cy.fixture("one-node.json").as("oneNode")
    cy.fixture("accordion.json").as("accordion")
    cy.fixture("deep-accordion.json").as("deep-accordion")
    cy.fixture("page.json").as("page")
    cy.fixture("slideshow.json").as("slideshow")
  })

  describe("In-place", () => {
    it("should be able to add/edit/delete multi-content child node in-place", () => {
      cy.setup()

      const node = {
        title: "Root",
        mediaType: "multi-content",
        presentationStyle: "accordion",
      }

      const expectParentModalOpen = () => {
        cy.getByTestId(`node-modal-header`)
          .contains(/Edit/i)
          .contains(node.title)
          .should("exist")
      }

      cy.server()
      cy.route("POST", `**/nodes`).as("addNode")
      cy.route("PUT", `**/nodes/**`).as("editNode")
      cy.route("DELETE", `**/nodes/**`).as("deleteNode")

      // Add multi-content node
      cy.getByTestId(`root-node-button`).click()
      cy.getByTestId(`node-title`).type(node.title)
      cy.getByTestId(`node-media-type`).select(node.mediaType)
      cy.getByTestId(`node-presentation-style`).select(node.presentationStyle)

      cy.getByTestId(`add-subitem`).should("be.disabled")
      cy.getByTestId(`save-node-button`).click()
      cy.wait("@addNode")

      expectParentModalOpen()

      // Add multi-content child node
      const child = {
        title: "Child",
        mediaType: "text",
      }

      cy.getByTestId(`add-subitem`).should("not.be.disabled")
      cy.getByTestId(`add-subitem`)
        .click()
        .then(() => {
          cy.getByTestId(`node-modal-header-back`)
            .contains(node.title)
            .should("exist")
          cy.getByTestId(`node-title`).type(child.title)
          cy.getByTestId(`node-media-type`).select(child.mediaType)
          cy.getByTestId("submit-node-modal").click()
          cy.wait("@addNode")
        })
        .then(() => {
          expectParentModalOpen()
          cy.getByTestId(`sub-item-table`).within(() => {
            cy.contains(child.title).should("exist")
          })
          cy.store()
            .its("state.nodes")
            .then(nodes => {
              const allNodes = Object.values(nodes)
              expect(allNodes.length).equals(2)
              expect(
                allNodes.filter(n => n.title === node.title)[0].childOrdering.length
              ).equals(1)
            })
        })

      // Go back to parent from multi-content child node
      cy.getByTestId(`sub-item-table`)
        .within(() => {
          cy.contains("Edit").click()
        })
        .then(() => {
          cy.getByTestId(`node-modal-header-back`).should("exist")
          cy.contains(child.title).should("exist")
        })
        .then(() => {
          cy.getByTestId(`node-modal-header-back`).click()
        })

      // Edit multi-content child node
      cy.getByTestId(`sub-item-table`)
        .within(() => {
          cy.contains("Edit").click()
        })
        .then(() => {
          child.title = "Child2"
          cy.getByTestId(`node-title`).clear()
          cy.getByTestId(`node-title`).type(child.title)
          cy.getByTestId("submit-node-modal").click()
          cy.wait("@editNode")
        })
        .then(() => {
          expectParentModalOpen()
          cy.getByTestId(`sub-item-table`).within(() => {
            cy.contains(child.title).should("exist")
          })
        })

      // Delete multi-content child node
      cy.getByTestId(`sub-item-table`)
        .within(() => {
          cy.contains("Edit").click()
        })
        .then(() => {
          cy.contains(/Delete Node/i).click()
          cy.wait("@deleteNode")
        })
        .then(() => {
          expectParentModalOpen()
          cy.store()
            .its("state.nodes")
            .then(nodes => {
              const allNodes = Object.values(nodes)
              expect(allNodes.length).equals(1)
            })
        })
    })
  })

  describe("Accordion", () => {
    it("should be able to make a node an accordion", () => {
      cy.setup("@oneNode")

      cy.getSelectedNode().then(node => {
        cy.openModal("edit", node.id)
        cy.changeMediaType("multi-content")
        cy.submitModal()

        cy.openLightbox(node.id).within(() => {
          cy.getByTestId("multi-content").should("be.visible")
          cy.contains(node.title).should("be.visible")
        })
      })
    })

    it("should be able to add child rows to an accordion", () => {
      cy.setup("@oneNode")

      cy.getSelectedNode().then(node =>
        cy.editNode(node.id, {
          mediaType: "multi-content",
          presentationStyle: "accordion",
        })
      )

      const rows = [
        {
          title: "row 1",
          typeData: {
            textContent: "hello world",
          },
        },
        {
          title: "row 2",
          typeData: {
            textContent: "bye world",
          },
        },
      ]

      cy.getSelectedNode().then(node => {
        for (const row of rows) {
          cy.addNode(node.id, row)
        }

        cy.openLightbox(node.id).within(() => {
          cy.getByTestId("accordion-rows")
            .find("div.accordion-row")
            .each(($el, index) => {
              const row = rows[index]
              cy.wrap($el)
                .contains(row.title)
                .should("exist")
            })

          for (const row of rows) {
            cy.contains(row.title).click()
            cy.contains(row.typeData.textContent).should("be.visible")
          }
        })
      })
    })

    it("should be able to lock accordion rows and progress through them", () => {
      cy.setup("@accordion")

      cy.store()
        .its("state.nodes")
        .then(nodes => {
          const [accordion, row1, row2] = Object.values(nodes)

          cy.openModal("edit", accordion.id)
          cy.contains(/lock rows/i).click()
          cy.submitModal()

          cy.openLightbox(accordion.id).within(() => {
            cy.contains(row1.title).should("not.be.disabled")
            cy.contains(row2.title).should("be.disabled")

            cy.contains(row1.title).click()
            cy.contains(row2.title).should("not.be.disabled")

            cy.contains(row2.title).click()
            cy.contains(row2.typeData.textContent).should("be.visible")
          })
        })
    })

    it("should be able to reorder accordion rows", () => {
      cy.setup("@accordion")

      cy.getSelectedNode().then(node => {
        const [row1, row2] = node.childOrdering
        const newOrdering = [row2, row1]

        cy.editNode(node.id, {
          childOrdering: newOrdering,
        })

        cy.openLightbox(node.id).within(() => {
          cy.getByTestId("accordion-rows")
            .find("div.accordion-row")
            .each(($el, index) => {
              const row = newOrdering[index]
              cy.store()
                .its(`state.nodes.${row}`)
                .then(rowNode => {
                  cy.wrap($el)
                    .contains(rowNode.title)
                    .should("exist")
                })
            })
        })
      })
    })

    it("should be able to add child nodes to accordion rows and have them appear as subaccordions", () => {
      cy.setup("@accordion")

      const row = {
        title: "sub row",
        typeData: {
          textContent: "hello world",
        },
      }

      cy.store()
        .its("state.nodes")
        .then(nodes => {
          const [root, child] = Object.values(nodes)
          cy.addNode(child.id, row)

          cy.openLightbox(root.id).within(() => {
            cy.contains(child.title).click()
            cy.getByTestId(`row-content-${child.id}`).within(() => {
              cy.contains(row.title).click()
              cy.contains(row.typeData.textContent).should("be.visible")
            })
          })
        })
    })

    // Structure: Accordion > Row > Sub-Row > Node
    it("should not be able to add a node to subaccordion row if accordion row is not an accordion", () => {
      cy.setup("@deep-accordion")

      cy.store()
        .its("state.nodes")
        .then(nodes => {
          const [, , grandchild] = Object.values(nodes)
          cy.getByTestId(`add-node-${grandchild.id}`).should("not.exist")
        })
    })

    it("should not show descendants to subscriber", () => {
      cy.setup("@accordion")

      const grandchild = {
        title: "grandchild",
        typeData: {
          textContent: "hello world",
        },
      }

      cy.store()
        .its("state.nodes")
        .then(nodes => {
          const [accordion, child] = Object.values(nodes)
          cy.addNode(child.id, grandchild)

          cy.logout().visitTapestry()
          cy.login("subscriber").visitTapestry()

          cy.contains(accordion.title).should("be.visible")
          cy.contains(child.title).should("not.be.visible")
          cy.contains(grandchild.title).should("not.be.visible")
        })
    })
  })

  describe("Page", () => {
    it("should render text content, more content and hide locked content", () => {
      cy.setup("@page")

      cy.store()
        .its("state.nodes")
        .then(nodes => {
          const [pageNode, videoNode, textNode, moreNode] = Object.values(nodes)

          cy.editNode(pageNode.id, {
            childOrdering: [videoNode.id, textNode.id],
          })

          cy.openLightbox(pageNode.id).within(() => {
            cy.get("iframe[id^=youtube]").should("exist")
            cy.contains(textNode.title).should("exist")
            cy.contains(textNode.typeData.textContent).should("exist")
            cy.contains(moreNode.title).should("exist")
          })
          cy.closeLightbox()

          cy.openModal("edit", pageNode.id)
          cy.getByTestId("lock-checkbox").click({ force: true })
          cy.submitModal()

          cy.openLightbox(pageNode.id).within(() => {
            cy.get("iframe[id^=youtube]").should("exist")
            cy.contains(textNode.title).should("exist")
            cy.contains(textNode.typeData.textContent).should("not.exist")
            cy.contains(moreNode.title).should("not.exist")
            cy.contains(moreNode.typeData.textContent).should("not.exist")
          })
        })
    })
  })

  describe("Slideshow", () => {
    it("should render, navigate unlocked slides, and disable nagivating to locked slides", () => {
      cy.setup("@slideshow")

      cy.store()
        .its("state.nodes")
        .then(nodes => {
          const [slideshowNode, textNode, videoNode, nextNode] = Object.values(nodes)
          const totalSlides = 3

          // Locked
          cy.openLightbox(slideshowNode.id).within(() => {
            cy.contains(textNode.title).should("exist")
            cy.contains(textNode.typeData.textContent).should("exist")
            cy.getByTestId("slideshow-prev-button").should("be.disabled")
            cy.getByTestId("slideshow-step").contains(`1/${totalSlides}`)

            cy.getByTestId("slideshow-next-button").click()
            cy.getByTestId("slideshow-step").contains(`2/${totalSlides}`)
            cy.get("iframe[id^=youtube]").should("exist")
            cy.contains(videoNode.title).should("exist")
            cy.getByTestId("slideshow-next-button").should("be.disabled")

            cy.getByTestId("slideshow-prev-button").click()
            cy.getByTestId("slideshow-step").contains(`1/${totalSlides}`)
            cy.contains(textNode.title).should("exist")
            cy.contains(textNode.typeData.textContent).should("exist")
          })
          cy.closeLightbox()

          cy.openModal("edit", slideshowNode.id)
          cy.getByTestId("lock-checkbox").click({ force: true })
          cy.submitModal()

          // Unlocked
          cy.openLightbox(slideshowNode.id).within(() => {
            cy.contains(textNode.title).should("exist")
            cy.contains(textNode.typeData.textContent).should("exist")
            cy.getByTestId("slideshow-prev-button").should("be.disabled")
            cy.getByTestId("slideshow-step").contains(`1/${totalSlides}`)

            cy.getByTestId("slideshow-next-button").click()
            cy.getByTestId("slideshow-step").contains(`2/${totalSlides}`)
            cy.get("iframe[id^=youtube]").should("exist")
            cy.contains(videoNode.title).should("exist")

            cy.getByTestId("slideshow-next-button").click()
            cy.getByTestId("slideshow-step").contains(`3/${totalSlides}`)
            cy.contains(nextNode.title).should("exist")
            cy.contains(nextNode.typeData.textContent).should("exist")
          })
        })
    })
  })
})
