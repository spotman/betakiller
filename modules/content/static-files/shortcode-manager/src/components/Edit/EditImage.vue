<template>
    <v-form v-if="modelDataReady" v-model="formValid">
        <img :src="getPreviewUrl" alt="" class="preview-image mb-4">
        <v-text-field v-model="modelData.alt" label="alt" @input="modelDataChanged" required/>
        <v-text-field v-model="modelData.title" label="title" @input="modelDataChanged"/>
    </v-form>
</template>

<script>
  import EditModelMixin from "./EditModelMixin";

  export default {
    name: "EditImage",
    mixins: [
      EditModelMixin
    ],

    computed: {
      getPreviewUrl() {
        return this.modelData.url;
      }
    }
  }
</script>

<style scoped>
    .preview-image {
        display: block;
        max-width: 100%;
        height: auto;
        margin-left: auto;
        margin-right: auto;
    }
</style>
