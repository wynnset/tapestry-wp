<template>
  <cos-modal show>
    <b-container class="confirm">
      <b-row align-h="center" class="py-2">
        <h2 class="ob-title">
          Now that we have your communities, we can add your connections.
        </h2>
      </b-row>
      <b-row align-h="center" class="py-2">
        <h4 class="ob-secondary">
          Connections are those who support you - think friends, family members,
          teammates, classmates, and even pets!
        </h4>
      </b-row>
      <b-row align-h="center" class="py-2">
        <b-button
          pill
          variant="secondary"
          class="secondary"
          @click="$emit('continue')"
        >
          Add Connection &#8594;
        </b-button>
      </b-row>
      <b-row align-h="center" class="py-2">
        <b-container>
          <b-row class="py-2">
            <b-col v-for="connection in connections" :key="connection.name">
              <h5>{{ connection.name }}</h5>
              <h1>{{ connection.icon }}</h1>
            </b-col>
          </b-row>
        </b-container>
      </b-row>
    </b-container>
  </cos-modal>
</template>

<script>
import CosModal from "../CosModal"
import { STARTER_CONNECTIONS } from "./onboarding.config"
export default {
  components: {
    CosModal,
  },
  computed: {
    connections() {
      return STARTER_CONNECTIONS
    },
  },
}
</script>
