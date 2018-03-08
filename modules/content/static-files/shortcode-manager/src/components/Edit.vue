<template>

    <div v-if="initialized">
        <!-- Display different views for different shortcodes -->
        <edit-gallery v-if="isGallery" :id="id"></edit-gallery>
        <edit-model v-else :id="id"></edit-model>
    </div>

</template>

<script>
  import {mapGetters} from 'vuex';
  import EditModel from './EditModel';
  import EditGallery from './EditGallery';

  export default {
    name: "edit",
    components: {
      EditModel,
      EditGallery
    },
    props: {
      id: {
        type: String,
        default: null
      },
    },
    computed: {
      ...mapGetters([
        'initialized',
        'shortcodeName',
      ]),

      // TODO Deal with ready events and switching current view
      // https://stackoverflow.com/questions/34527625/how-to-swich-current-view-with-vue-js
      isGallery() {
        console.log(this.shortcodeName);
        return this.shortcodeName.toLowerCase() === 'gallery';
      }
    }
  }
</script>

<style scoped>

</style>
