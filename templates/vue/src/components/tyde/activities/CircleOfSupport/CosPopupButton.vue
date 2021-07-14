<template>
  <span
    class="popup-button"
    v-bind="$attrs"
    :style="{ cursor: disabled ? 'not-allowed' : 'pointer' }"
    @click="$emit('click', $event)"
  >
    <button :disabled="disabled">
      <slot></slot>
    </button>
  </span>
</template>

<script>
export default {
  props: {
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
}
</script>

<style scoped lang="scss">
.popup-button {
  --size: 5rem;

  width: var(--size);
  height: var(--size);
  background: var(--cos-color-tertiary);
  position: absolute;
  top: calc(-1 * var(--size));
  border-top-left-radius: var(--size);
  border-top-right-radius: var(--size);
  font-size: 2.5rem;
  transform: translateY(20%);
  z-index: 0;
  display: flex;
  justify-content: center;
}

button {
  padding: 0;
  margin: 0;
  background: none;

  &[disabled] {
    pointer-events: none;
  }
}
</style>
