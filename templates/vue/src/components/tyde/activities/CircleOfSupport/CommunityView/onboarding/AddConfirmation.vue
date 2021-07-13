<template>
  <cos-modal show @change="$emit('later')">
    <b-container class="confirm">
      <b-row align-h="center" class="py-2">
        <h2 class="ob-title">Great job! Here are all your communities.</h2>
      </b-row>
      <b-row align-h="center" class="py-2">
        <h4 class="ob-secondary">Would you like to add another community?</h4>
      </b-row>
      <b-row>
        <b-container>
          <b-row class="controls py-2">
            <b-col>
              <b-button
                pill
                variant="secondary"
                class="secondary"
                @click="$emit('later')"
              >
                Maybe later &#8594;
              </b-button>
            </b-col>
            <b-col>
              <b-button
                pill
                variant="secondary"
                class="primary"
                @click="$emit('another')"
              >
                Add another &#8594;
              </b-button>
            </b-col>
          </b-row>
        </b-container>
      </b-row>
    </b-container>
  </cos-modal>
</template>

<script>
import CosModal from "../CosModal"
export default {
  components: {
    CosModal,
  },
}
</script>
