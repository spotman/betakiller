<template>
    <v-layout row wrap>
        <v-flex xs12>
            <!-- All available images -->
            <ItemsIndex
                    shortcodeName="Image"
                    :showGlobal="false"
                    :selectable="true"
                    :sortable="true"
                    :selected="selectedItemsIDs"
                    @selectItem="addItem"
                    @deselectItem="removeItem"
            />
        </v-flex>
        <v-flex xs12>
            <!-- TODO "Save" button -->
        </v-flex>
    </v-layout>
</template>

<script>
  import ItemsIndex from './../ItemsIndex';
  import EditModelMixin from './EditModelMixin';
  export default {
    name: "EditGallery",

    mixins: [
      EditModelMixin
    ],

    components: {
      ItemsIndex,
    },

    computed: {
      selectedItemsIDs() {
        return this.modelData.images;
      }
    },

    methods: {
      addItem(id) {
        this.modelData.images = this.modelData.images.concat([id]);
      },
      removeItem(id) {
        this.modelData.images = this.modelData.images.filter(e => e !== id);
      }
    }
  }
</script>

<style scoped>

</style>
