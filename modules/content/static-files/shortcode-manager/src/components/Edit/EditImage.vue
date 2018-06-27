<template>
    <v-layout v-if="modelDataReady">
        <v-flex v-if="getPreviewUrl" xs12 md6 lg4 xl3 text-xs-center>
            <img :src="getPreviewUrl" alt="Preview image" class="preview-image mb-4">
        </v-flex>

        <v-flex xs12 md6 lg4 xl3 text-xs-center>
            <v-form v-model="formValid">
                <v-text-field v-model="modelData.alt" label="alt" @input="modelDataChanged" required/>
                <v-text-field v-model="modelData.title" label="title" @input="modelDataChanged"/>
            </v-form>
        </v-flex>

        <!-- TODO Deal with edit layout and shortcode attributes -->

    </v-layout>
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
</style>
