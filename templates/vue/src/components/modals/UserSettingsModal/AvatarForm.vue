<template>
  <div>
    <b-overlay variant="white">
      <div class="title-and-avatar">
        <div class="title">
          <h2>Avatar Customizer</h2>
        </div>
        <div class="avatar">
          <avataaars
            v-if="hasAvatar"
            :isCircle="userAvatar.isCircle"
            :circleColor="userAvatar.circleColor"
            :accessoriesType="userAvatar.accessoriesType"
            :clotheType="userAvatar.clotheType"
            :clotheColor="userAvatar.clotheColor"
            :eyebrowType="userAvatar.eyebrowType"
            :eyeType="userAvatar.eyeType"
            :facialHairColor="userAvatar.facialHairColor"
            :facialHairType="userAvatar.facialHairType"
            :graphicType="userAvatar.graphicType"
            :hairColor="userAvatar.hairColor"
            :mouthType="userAvatar.mouthType"
            :skinColor="userAvatar.skinColor"
            :topType="userAvatar.topType"
            :topColor="userAvatar.topColor"
          ></avataaars>
        </div>
      </div>
      <div class="customizer">
        <b-card no-body>
          <b-tabs pills card fill>
            <b-tab title="Background" active>
              <b-card-text>
                <b-form-group label="Background Shape" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.isCircle"
                    :options="isCircleOptions"
                    data-qa="avatar-background-select"
                  ></b-form-select>
                </b-form-group>
                <b-form-group
                  v-if="userAvatar.isCircle"
                  label="Circle Colour"
                  label-for="input-horizontal"
                >
                  <b-form-select
                    v-model="userAvatar.circleColor"
                    :options="circleColorOptions"
                  ></b-form-select>
                </b-form-group>
              </b-card-text>
            </b-tab>
            <b-tab title="Face">
              <b-card-text>
                <b-form-group label="Skin Colour" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.skinColor"
                    :options="skinColorOptions"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Eyebrow Type" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.eyebrowType"
                    :options="eyebrowTypeOptions"
                    data-qa="avatar-eyebrow-select"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Eye Type" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.eyeType"
                    :options="eyeTypeOptions"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Mouth Type" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.mouthType"
                    :options="mouthTypeOptions"
                  ></b-form-select>
                </b-form-group>
              </b-card-text>
            </b-tab>
            <b-tab title="Hair">
              <b-card-text>
                <b-form-group label="Hair Type" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.topType"
                    :options="topTypeOptions"
                    data-qa="avatar-hair-select"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Hair Colour" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.hairColor"
                    :options="hairColorOptions"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Hat Colour" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.topColor"
                    :options="topColorOptions"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Facial Hair Type" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.facialHairType"
                    :options="facialHairTypeOptions"
                  ></b-form-select>
                </b-form-group>
                <b-form-group
                  label="Facial Hair Colour"
                  label-for="input-horizontal"
                >
                  <b-form-select
                    v-model="userAvatar.facialHairColor"
                    :options="facialHairColorOptions"
                  ></b-form-select>
                </b-form-group>
              </b-card-text>
            </b-tab>
            <b-tab title="Clothing">
              <b-card-text>
                <b-form-group label="Glasses" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.accessoriesType"
                    :options="accessoriesTypeOptions"
                    data-qa="avatar-glasses-select"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Clothing Type" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.clotheType"
                    :options="clotheTypeOptions"
                  ></b-form-select>
                </b-form-group>
                <b-form-group
                  v-if="userAvatar.clotheType == 'GraphicShirt'"
                  label="T-Shirt Graphics"
                  label-for="input-horizontal"
                >
                  <b-form-select
                    v-model="userAvatar.graphicType"
                    :options="graphicTypeOptions"
                  ></b-form-select>
                </b-form-group>
                <b-form-group label="Clothing Colour" label-for="input-horizontal">
                  <b-form-select
                    v-model="userAvatar.clotheColor"
                    :options="clotheColorOptions"
                  ></b-form-select>
                </b-form-group>
              </b-card-text>
            </b-tab>
          </b-tabs>
        </b-card>
      </div>
    </b-overlay>
  </div>
</template>

<script>
import Avataaars from "vuejs-avataaars"
import avatarOptions from "./avatarOptions"
import { mapActions } from "vuex"

export default {
  name: "avatar-form",
  components: {
    Avataaars,
  },
  props: {
    preferences: {
      type: Object,
      required: false,
      default: () => {},
    },
  },
  data() {
    return {
      userAvatar: {},
      isCircleOptions: avatarOptions.isCircleOptions,
      circleColorOptions: avatarOptions.circleColorOptions,
      accessoriesTypeOptions: avatarOptions.accessoriesTypeOptions,
      clotheTypeOptions: avatarOptions.clotheTypeOptions,
      clotheColorOptions: avatarOptions.clotheColorOptions,
      eyebrowTypeOptions: avatarOptions.eyebrowTypeOptions,
      eyeTypeOptions: avatarOptions.eyeTypeOptions,
      facialHairColorOptions: avatarOptions.facialHairColorOptions,
      facialHairTypeOptions: avatarOptions.facialHairTypeOptions,
      graphicTypeOptions: avatarOptions.graphicTypeOptions,
      hairColorOptions: avatarOptions.hairColorOptions,
      mouthTypeOptions: avatarOptions.mouthTypeOptions,
      skinColorOptions: avatarOptions.skinColorOptions,
      topTypeOptions: avatarOptions.topTypeOptions,
      topColorOptions: avatarOptions.topColorOptions,
    }
  },
  computed: {
    hasAvatar() {
      return !(!this.userAvatar || Object.keys(this.userAvatar).length === 0)
    },
  },
  created() {
    if (!this.preferences || Object.keys(this.preferences).length === 0) {
      this.userAvatar = {
        isCircle: true,
        circleColor: "#6fb8e0",
        topType: "NoHair",
        accessoriesType: "Blank",
        facialHairType: "Blank",
        clotheType: "ShirtCrewNeck",
        eyeType: "Default",
        eyebrowType: "Default",
        mouthType: "Default",
        skinColor: "Tanned",
        graphicType: "Bat",
        hairColor: "Black",
        facialHairColor: "Black",
        clotheColor: "Black",
        topColor: "Black",
      }
    } else {
      this.userAvatar = { ...this.preferences }
    }
  },
  methods: {
    ...mapActions(["updateUserSettings"]),
    saveAvatar() {
      this.updateUserSettings({ avatar: this.userAvatar })
    },
  },
}
</script>

<style scoped>
.title {
  text-align: center;
}

.avatar {
  display: flex;
  justify-content: center;
  width: 300px;
  margin: 0 auto;
}

.customizer {
  margin-top: 50px;
}
</style>
