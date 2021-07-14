<template>
  <div class="content">
    <b-overlay :show="isLoading" style="width: 100%">
      <form @submit.stop.prevent>
        <div class="community" style="flex: 1">
          <div class="community-name">
            <input
              :value="community.name"
              name="community name"
              type="text"
              placeholder="community"
              @input="handleChange('name', $event.target.value)"
              @blur="isInputTouched = true"
            />
            <p :style="{ '--color': countColor }">{{ characterCount }}</p>
          </div>
          <b-form-invalid-feedback :state="hideErrorMessage">
            {{ errorMessages[validationState.type] }}
          </b-form-invalid-feedback>
          <div id="emoji-picker" style="position: relative">
            <button class="preview" @click="showPicker = !showPicker">
              {{ community.icon }}
            </button>
            <div v-show="showPicker" class="picker" data-qa="emoji-picker">
              <v-emoji-picker @select="handleChange('icon', $event.data)" />
            </div>
          </div>
          <div class="controls">
            <button @click="$emit('back')">Cancel</button>
            <button
              class="submit"
              :disabled="validationState"
              @click="saveCommunity"
            >
              {{ submitLabel }}
            </button>
          </div>
        </div>
        <div
          class="community-preview"
          :style="{ flex: 1, '--color': community.color }"
        ></div>
        <ul class="colors" style="flex: 1">
          <li v-for="color in colors" :key="color" :style="{ '--color': color }">
            <button
              :class="['color', { active: color === community.color }]"
              @click="handleChange('color', color)"
            ></button>
          </li>
        </ul>
      </form>
    </b-overlay>
  </div>
</template>

<script>
import { VEmojiPicker } from "v-emoji-picker"
import client from "@/services/TapestryAPI"
import { MAX_COMMUNITY_NAME_LENGTH } from "../cos.config"

export default {
  components: {
    VEmojiPicker,
  },
  model: {
    prop: "community",
    event: "change",
  },
  props: {
    community: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      showPicker: false,
      isLoading: false,
      isInputTouched: false,
    }
  },
  computed: {
    colors() {
      return [
        "#FF7878",
        "#FDAB96",
        "#FF9A3E",
        "#FDDC80",
        "#EDF138",
        "#95FDBD",
        "#4EB945",
        "#3DF1C6",
        "#EC79D3",
        "#C182F2",
        "#8E77EB",
        "#5F3AF1",
        "#105FFA",
        "#4BB0F9",
        "#68DCF5",
        "#9AF8EC",
      ]
    },
    isEdit() {
      return Boolean(this.community.id)
    },
    submitLabel() {
      return this.isEdit ? "Save community" : "Add community"
    },
    remainingCharacters() {
      return MAX_COMMUNITY_NAME_LENGTH - this.community.name.length
    },
    characterCount() {
      return `${this.remainingCharacters} / ${MAX_COMMUNITY_NAME_LENGTH}`
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
        TOO_LONG: `Please shorten the community name to not more than ${MAX_COMMUNITY_NAME_LENGTH} characters.`,
        EMPTY: "Please enter a name for your community",
      }
    },
    validationState() {
      if (this.community.name.length > MAX_COMMUNITY_NAME_LENGTH) {
        return {
          type: "TOO_LONG",
          display: true,
        }
      }
      if (this.community.name.length === 0) {
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
    handleChange(prop, value) {
      this.$emit("change", { ...this.community, [prop]: value })
    },
    saveCommunity() {
      this.isInputTouched = true
      this.$nextTick(async () => {
        if (this.validationState) {
          return
        }
        this.isLoading = true
        const community = this.isEdit
          ? await client.cos.updateCommunity(
              this.community.id,
              this.cleanCommunity(this.community)
            )
          : await client.cos.addCommunity(this.community)
        // Reset the community object
        this.$emit("change", {
          name: "",
          icon: "👨‍👩‍👦",
          color: "",
        })
        this.isLoading = false
        this.isInputTouched = false
        this.$emit("add-community", community)
      })
    },
    /**
     * Clean up the community properties to only those that match the schema in the
     * README.
     *
     * This is necessary because we add other properties to render the community
     * correctly in <communities-list>.
     */
    cleanCommunity(community) {
      const { id, name, icon, color, connections } = community
      return {
        id,
        name,
        icon,
        color,
        connections: connections.map(connection =>
          typeof connection !== "string" ? connection.id : connection
        ),
      }
    },
  },
}
</script>

<style scoped lang="scss">
.content {
  height: 100%;
  display: flex;
}

form {
  display: flex;
  column-gap: clamp(1rem, 8vw, 3rem);
  height: 100%;
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

.community {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  justify-content: space-between;
}

.community-name {
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

.colors {
  border: var(--cos-border);
  border-radius: 1.5rem;
  padding: 1rem 2rem;
  height: 100%;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  column-gap: 0.5rem;
  row-gap: 1rem;
  align-items: center;
}

.color {
  background: var(--color);
  width: clamp(2rem, 5vw, 4rem);
  height: clamp(2rem, 5vw, 4rem);
  border-radius: 50%;
  position: relative;

  &:after {
    content: "";
    position: absolute;
    width: 120%;
    height: 120%;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    border: 4px solid var(--color);
    border-radius: 50%;
    opacity: 0;
  }

  &.active,
  &:hover {
    &:after {
      opacity: 1;
    }
  }
}

.community-preview {
  background: var(--color, var(--cos-color-secondary));
  margin-bottom: -3rem;
  border-top-left-radius: 9999px;
  border-top-right-radius: 9999px;
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
  z-index: 10;
}

.controls {
  color: #757575;
  display: flex;
  column-gap: 1rem;
  font-size: 1.2em;
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
