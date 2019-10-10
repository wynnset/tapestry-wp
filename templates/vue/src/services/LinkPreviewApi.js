import axios from "axios"
import { LINK_PREVIEW_API_KEY } from "../../config"

const API_URL = `http://api.linkpreview.net`

export async function getLinkMetadata(url) {
  const endpoint = `${API_URL}/?key=${LINK_PREVIEW_API_KEY}&q=${normalizeUrl(url)}`
  try {
    const { data } = await axios.get(endpoint)
    return { data }
  } catch (error) {
    return { error }
  }
}

function normalizeUrl(url) {
  return url.startsWith("http:") || url.startsWith("https:") ? url : `https:${url}`
}
