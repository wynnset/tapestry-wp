<template>
  <div>
    <b-form-group label="Presentation Style">
      <b-form-select
        id="node-presentation-style"
        data-qa="node-presentation-style"
        :value="node.presentationStyle ? node.presentationStyle : 'accordion'"
        :options="presentationStyles"
        @change="handlePresentationChange"
      ></b-form-select>
    </b-form-group>
    <sub-item-table :actionType="actionType" :node="node"></sub-item-table>
    <b-form-group>
      <b-form-checkbox v-model="node.typeData.lockRows" data-qa="lock-checkbox">
        Lock rows until previous row is completed
      </b-form-checkbox>
    </b-form-group>
    <b-form-group label="Finish button text">
      <b-form-input v-model="node.typeData.finishButtonText"></b-form-input>
    </b-form-group>
    <b-form-group label="Confirmation title text">
      <b-form-input v-model="node.typeData.confirmationTitleText"></b-form-input>
    </b-form-group>
    <b-form-group label="Confirmation body text">
      <b-form-input v-model="node.typeData.confirmationBodyText"></b-form-input>
    </b-form-group>
    <b-form-group label="Continue button text">
      <b-form-input v-model="node.typeData.continueButtonText"></b-form-input>
    </b-form-group>
    <b-form-group label="Cancel link text">
      <b-form-input v-model="node.typeData.cancelLinkText"></b-form-input>
    </b-form-group>
  </div>
</template>

<script>
import SubItemTable from "./SubItemTable.vue"

export default {
  name: "multi-content-form",
  components: { SubItemTable },
  props: {
    node: {
      type: Object,
      required: true,
    },
    actionType: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      presentationStyles: [
        { value: "accordion", text: "Accordion" },
        { value: "page", text: "Page" },
        { value: "slideshow", text: "Slideshow" },
      ],
    }
  },
  mounted() {
    // set node defaults
    this.node.typeData = {
      lockRows: false,
      finishButtonText: "Finish",
      confirmationTitleText: "Section Complete!",
      confirmationBodyText: "Would you like to continue?",
      continueButtonText: "Continue",
      cancelLinkText: "Cancel",
      showNavBar: true,
      ...this.node.typeData,
    }
  },
  methods: {
    handlePresentationChange(evt) {
      this.node.presentationStyle = evt
    },
  },
}
</script>
