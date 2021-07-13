<template>
  <div v-show="show" class="modal ob-font">
    <div class="overlay" @click="$emit('change', false)"></div>
    <div class="content center">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  model: {
    prop: "show",
    event: "change",
  },
  props: {
    show: {
      type: Boolean,
      required: true,
    },
  },
}
</script>

<style scoped>
.modal {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  display: flex;
  align-items: flex-start;
}
.overlay {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  background: black;
  opacity: 0.4;
}
.content {
  position: relative;
  padding: 2rem;
  background: white;
  border: var(--cos-border);
  border-radius: 1rem;
  max-width: 700px;
}
</style>
