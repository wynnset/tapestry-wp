<template>
  <div>
    <b-form-group>
      <b-form-checkbox v-model="node.isDyad">
        Enable dyad node behaviour
      </b-form-checkbox>
    </b-form-group>
    <small id="emailHelp" class="form-text text-muted">
      When checked, you will be able to see your child's progress in this node. Only
      the child's progress will count towards this node's completion.
    </small>
  </div>
</template>

<script>
export default {
  name: "dyad-form",
  props: {
    node: {
      type: Object,
      required: true,
    },
  },
}
</script>
