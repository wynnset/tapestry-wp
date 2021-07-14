<template>
  <div :class="['wrapper', { show }]">
    <slot name="toggle"></slot>
    <slot name="content"></slot>
  </div>
</template>

<script>
export default {
  props: {
    show: {
      type: Boolean,
      required: true,
    },
  },
}
</script>

<style lang="scss" scoped>
.wrapper {
  transform: translateY(100%);
  transition: transform 0.3s ease-out;
  min-height: 16rem;
  max-height: 80%;
  display: flex;
  flex-direction: column;
  z-index: 50;

  &.show {
    z-index: 60;
    transform: translateY(0);
  }
}
</style>
