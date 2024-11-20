<template>
    <v-layout v-if="modelDataReady">
        <v-flex v-if="previewImageUrl" xs12 md6 lg4 xl3 text-xs-center>
            <img :src="previewImageUrl" alt="Preview image" class="preview-image mb-4">
        </v-flex>

        <v-flex xs12 md6 lg4 xl3 text-xs-center slot="model" />

        <v-flex xs12 md6 lg4 xl3 text-xs-center slot="attributes" />
    </v-layout>
</template>

<script>
  export default {
    name: "EditItemLayout",
    props: {
      previewImageUrl: {
        type: String,
        default: null
      },
    },
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
