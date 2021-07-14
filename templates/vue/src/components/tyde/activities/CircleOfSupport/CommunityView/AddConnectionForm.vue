<template>
  <div>
    <add-community-form
      v-show="showCommunityForm"
      v-model="community"
      @back="showCommunityForm = false"
      @add-community="handleAddCommunity"
    />
    <form v-show="!showCommunityForm" style="height: 100%" @submit.stop.prevent>
      <div class="connection" style="flex: 1">
        <div class="connection-name">
          <input
            v-model="connection.name"
            name="connection name"
            type="text"
            placeholder="connection"
            @blur="isInputTouched = true"
          />
          <p :style="{ '--color': countColor }">{{ characterCount }}</p>
          <b-form-invalid-feedback :state="hideErrorMessage">
            {{ errorMessages[validationState.type] }}
          </b-form-invalid-feedback>
        </div>
        <div id="emoji-picker" style="position: relative">
          <button class="preview" @click="showPicker = !showPicker">
            {{ connection.avatar }}
          </button>
          <div v-show="showPicker" class="picker" data-qa="emoji-picker">
            <picker :data="emojiIndex" title="Pick your emoji…" emoji="point_up" />
          </div>
        </div>
        <div class="controls">
          <button @click="$emit('back')">Cancel</button>
          <button
            class="submit"
            :disabled="validationState"
            @click="submitConnection"
          >
            {{ submitLabel }}
          </button>
        </div>
      </div>
      <div class="community" style="flex: 2">
        <h1 class="community-title">
          Which communities does this person belong to?
        </h1>
        <ul class="community-list">
          <li v-for="existingCommunity in communities" :key="existingCommunity.id">
            <button
              :class="[
                'community-item',
                { selected: connection.communities.includes(existingCommunity.id) },
              ]"
              :style="`--color: ${existingCommunity.color}`"
              @click="toggleCommunity(existingCommunity.id)"
            >
              <span class="community-color"></span>
              <span class="community-name">
                {{ existingCommunity.name }}
              </span>
            </button>
          </li>
          <li v-if="Object.keys(communities).length < maxCommunitiesCount">
            <button class="community-item" @click="showCommunityForm = true">
              <span class="community-color" style="color: var(--cos-color-tertiary)">
                <tapestry-icon icon="plus" />
              </span>
              <span class="community-name" style="color: var(--cos-color-tertiary)">
                Add new
              </span>
            </button>
          </li>
        </ul>
      </div>
    </form>
  </div>
</template>

<script>
import TapestryIcon from "@/components/common/TapestryIcon"
import AddCommunityForm from "./AddCommunityForm"
import { MAX_COMMUNITIES, MAX_CONNECTION_NAME_LENGTH } from "../cos.config"
import data from "emoji-mart-vue-fast/data/all.json"
import { Picker, EmojiIndex } from "emoji-mart-vue-fast"
let emojiIndex = new EmojiIndex(data)

export default {
  components: {
    AddCommunityForm,
    TapestryIcon,
  },
  model: {
    prop: "connection",
    event: "change",
  },
  props: {
    communities: {
      type: Object,
      required: true,
    },
    connection: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      showPicker: false,
      isInputTouched: false,
      showCommunityForm: false,
      community: {
        name: "",
        icon: "👨‍👩‍👦",
        color: "",
      },
    }
  },
  computed: {
    submitLabel() {
      return this.connection.id ? "Save connection" : "Add connection"
    },
    maxCommunitiesCount() {
      return MAX_COMMUNITIES
    },
    remainingCharacters() {
      return MAX_CONNECTION_NAME_LENGTH - this.connection.name.length
    },
    characterCount() {
      return `${this.remainingCharacters} / ${MAX_CONNECTION_NAME_LENGTH}`
    },
    countColor() {
      return this.remainingCharacters < 0
        ? `var(--danger)`
        : this.remainingCharacters < 5
        ? `var(--warning)`
        : `var(--secondary)`
    },
    errorMessages() {
      return {
        TOO_LONG: `Please shorten the connection name to not more than ${MAX_CONNECTION_NAME_LENGTH} characters.`,
        EMPTY: "Please enter a name for your connection",
      }
    },
    validationState() {
      if (this.connection.name.length > MAX_CONNECTION_NAME_LENGTH) {
        return {
          type: "TOO_LONG",
          display: true,
        }
      }
      if (this.connection.name.length === 0) {
        return {
          type: "EMPTY",
          display: this.isInputTouched,
        }
      }
      return false
    },
    hideErrorMessage() {
      return this.validationState && !this.validationState.display
    },
  },
  mounted() {
    const handleClick = evt => {
      if (!evt.target.closest("#emoji-picker")) {
        this.showPicker = false
      }
    }
    document.addEventListener("click", handleClick)
    this.$once("hook:destroyed", () => {
      document.removeEventListener("click", handleClick)
    })
  },
  methods: {
    toggleCommunity(communityId) {
      if (this.connection.communities.includes(communityId)) {
        this.connection.communities = this.connection.communities.filter(
          id => id !== communityId
        )
      } else {
        this.connection.communities.push(communityId)
      }
    },
    handleEmojiSelect(event) {
      this.connection.avatar = event.data
      this.showPicker = false
    },
    submitConnection() {
      this.isInputTouched = true

      this.$nextTick(() => {
        if (this.validationState) {
          return
        }
        this.isInputTouched = false
        this.$emit("submit")
      })
    },
    handleAddCommunity(community) {
      this.$emit("add-community", community)
      this.showCommunityForm = false
    },
  },
}
</script>

<style scoped lang="scss">
form {
  display: flex;
  column-gap: 3rem;
  padding: 2rem;
}

ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

button {
  background: none;
  margin: 0;
  padding: 0;
  color: inherit;
}

.connection-name {
  position: relative;

  input {
    font-size: 1.5rem;
    text-align: center;
    text-transform: uppercase;
    display: block;
    font-weight: 500;
    width: 100%;
  }

  p {
    position: absolute;
    margin: 0;
    top: 100%;
    right: 0;
    font-size: 0.9em;
    color: var(--color);
  }
}

.community {
  border: var(--cos-border);
  border-radius: 1.5rem;
  padding: 1rem 2rem 1rem 0;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.community-title {
  font-size: 1.5rem;
  font-weight: 400;
  color: #7f88af;
  margin-bottom: 2rem;
}

.community-list {
  height: 100%;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  grid-template-rows: repeat(5, 1fr);
  row-gap: 1rem;
  column-gap: 3rem;
}

.community-item {
  display: flex;
  align-items: center;
  width: 100%;
  height: 100%;
  column-gap: 1.5rem;
  color: var(--color, var(--cos-color-secondary));
}

.community-item:hover .community-color {
  background: currentColor;
}

.community-item.selected .community-color {
  background: currentColor;
}

.community-color {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid currentColor;
  height: 100%;
  width: 4rem;
  border-top-right-radius: 2rem;
  border-bottom-right-radius: 2rem;
}

.community-name {
  display: block;
  border: 1px solid var(--cos-color-tertiary);
  width: 100%;
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
  font-weight: 500;
  text-transform: uppercase;
  color: black;
  font-size: 1.25rem;
  overflow-x: scroll;
}

.connection {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  justify-content: space-between;
}

.preview {
  font-size: clamp(7rem, 10vw, 10rem);
  line-height: 1;
}

.picker {
  position: absolute;
  left: calc(100% + 2rem);
  top: 50%;
  transform: translateY(-55%);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.controls {
  color: #757575;
  display: flex;
  column-gap: 1rem;
}

.submit {
  background: #757575;
  color: white;
  border-radius: 0.5rem;
  padding: 0.5rem;
}

.submit:disabled {
  cursor: not-allowed;
  background: var(--cos-color-tertiary);
}

.submit:hover:not(:disabled) {
  background: var(--cos-color-secondary);
  color: white;
}
</style>

<style>
.emoji.border {
  border: none !important;
}
</style>
