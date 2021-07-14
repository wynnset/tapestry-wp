<template>
  <div>
    <emoji :data="index" emoji=":santa::skin-tone-3:" :size="32" />
    <emoji :data="index" emoji="santa" set="twitter" :size="32" />
    <emoji :data="index" :emoji="santaEmojiObject" :size="32" />
  </div>
</template>

<script>
import data from 'emoji-mart-vue-fast/data/all.json'
import { Emoji, EmojiIndex } from 'emoji-mart-vue-fast'
let index = new EmojiIndex(data)

export default {
  computed: {
    santaEmojiObject() {
      return index.findEmoji(':santa:')
    },
  },
}
</script>
