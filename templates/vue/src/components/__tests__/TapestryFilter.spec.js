import { waitFor, within } from "@testing-library/vue"
import userEvent from "@testing-library/user-event"
import { render as r } from "@/utils/test"
import { nodeStatus } from "@/utils/constants"
import Helpers from "@/utils/Helpers"
import TapestryFilter from "@/components/TapestryFilter.vue"
import multiAuthorTapestry from "@/fixtures/multi-author.json"
import * as wp from "@/services/wp"
import client from "@/services/TapestryAPI"
import { names } from "@/config/routes"

jest.mock("@/services/TapestryAPI", () => {
  return {
    ...jest.requireActual("@/services/TapestryAPI"),
    getAllContributors: jest.fn().mockResolvedValue(null),
    getTapestry: jest.fn(),
  }
})

const render = (settings = {}, query = {}) => {
  return r(
    TapestryFilter,
    {
      fixture: multiAuthorTapestry,
      settings,
    },
    (_vm, _store, router) => {
      router.push({
        name: names.APP,
        params: { nodeId: multiAuthorTapestry.nodes[0].id },
        query,
      })
    }
  )
}

const setup = settings => {
  const screen = render(settings)
  userEvent.click(screen.getByLabelText("search"))
  return screen
}

describe("TapestryFilter", () => {
  it("should be able to change the filter type and have the text placeholder update", async () => {
    const screen = setup()

    await screen.findByPlaceholderText("Node title")

    const select = screen.getByDisplayValue("Title")
    userEvent.click(select)

    const options = ["Title", "Author", "Status"]
    options.forEach(option => screen.getByText(option))

    userEvent.selectOptions(screen.getByDisplayValue("Title"), "Author")
    await screen.findByPlaceholderText("Node author")
  })

  it("should show dropdown of node titles if searching by title", async () => {
    const titles = multiAuthorTapestry.nodes.map(node => node.title)
    const screen = setup()

    userEvent.click(await screen.findByPlaceholderText("Node title"))
    await waitFor(() => {
      titles.forEach(title => screen.getByText(title))
    })
  })

  it("should show dropdown of authors if searching by author", async () => {
    const authors = Helpers.unique(
      multiAuthorTapestry.nodes.map(node => node.author),
      "id"
    )
    const screen = setup()

    userEvent.selectOptions(await screen.findByDisplayValue("Title"), "Author")
    userEvent.click(await screen.findByPlaceholderText("Node author"))

    await waitFor(() => {
      authors.forEach(author => screen.getByText(author.name))
    })
  })

  it("should be able to search for author by id", async () => {
    const { author } = multiAuthorTapestry.nodes[0]
    const screen = setup()

    userEvent.selectOptions(await screen.findByDisplayValue("Title"), "Author")
    userEvent.type(await screen.findByPlaceholderText("Node author"), author.id)

    await screen.findByText(author.name)
  })

  it("should show simple select if searching by status", async () => {
    const statuses = Object.values(nodeStatus)
    const screen = setup()

    userEvent.selectOptions(await screen.findByDisplayValue("Title"), "Status")
    await screen.findByTestId("status-select")

    userEvent.click(screen.getByText("All"))
    statuses.forEach(status => screen.getByText(status))
  })

  it("should reset value if search type is changed", async () => {
    const screen = setup()

    userEvent.selectOptions(await screen.findByDisplayValue("Title"), "Author")
    userEvent.click(await screen.findByPlaceholderText("Node author"))
    userEvent.click(await screen.findByText("admin"))

    userEvent.selectOptions(screen.getByDisplayValue("Author"), "Title")
    expect(await screen.findByPlaceholderText("Node title")).toHaveValue("")
  })

  it("should reset value to 'All' if search type is changed to status", async () => {
    const screen = setup()

    userEvent.selectOptions(await screen.findByDisplayValue("Title"), "Author")
    userEvent.click(await screen.findByPlaceholderText("Node author"))
    userEvent.click(await screen.findByText("admin"))

    userEvent.selectOptions(screen.getByDisplayValue("Author"), "Status")
    await screen.findByText("All")
  })

  it("should show loading indicator when superuser override is off", async () => {
    client.getTapestry.mockResolvedValue(multiAuthorTapestry)
    const screen = setup({ superuserOverridePermissions: false })

    userEvent.selectOptions(await screen.findByDisplayValue("Title"), "Author")
    userEvent.click(await screen.findByPlaceholderText("Node author"))
    userEvent.click(await screen.findByText("admin"))

    await screen.findByTestId("search-loading")
    await waitFor(() => {
      expect(screen.queryByTestId("search-loading")).toBeNull()
    })
  })

  it("should hide the search bar for unauthorized users", async () => {
    wp.canEditTapestry.mockReturnValueOnce(false)
    const screen = render()
    expect(screen.queryByLabelText("search")).toBeNull()
  })

  it("should show the search bar with the correct type if the user visits the url", async () => {
    const screen = render({}, { search: "Title" })
    expect(await screen.findByDisplayValue("Title")).toBeVisible()
  })

  it("should populate the value with the query in the url", async () => {
    const { author } = multiAuthorTapestry.nodes[0]
    const screen = render({}, { search: "Author", q: author.name })
    const filter = within(screen.getByTestId("tapestry-filter"))
    expect(await filter.findByText(author.name)).toBeVisible()
  })

  it("should not show the search bar if an unauthorized user visits the url", async () => {
    wp.canEditTapestry.mockReturnValueOnce(false)
    const screen = render({}, { search: "Author" })

    expect(screen.queryByLabelText("search")).toBeNull()
    expect(screen.queryByDisplayValue("Author")).toBeNull()
  })
})
