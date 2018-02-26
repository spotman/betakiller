<template>
    <v-container fluid v-if="hasItems">
        <v-layout row wrap v-masonry item-selector=".content-element-list-item" gutter=".content-element-list-item">
            <!--<div transition-duration="0.3s" item-selector=".content-element-list-item" >-->
            <v-flex xs6 sm4 md3 lg2 v-for="item in items" class="content-element-list-item" :key="item.id" v-masonry-tile>
                <v-card elevation-1>
                    <img :src="item.imageUrl" alt="">
                    <span v-if="!item.isValid" class="invalid-marker material-icons red--text">error</span>
                </v-card>
            </v-flex>
        </v-layout>
    </v-container>
    <v-container v-else-if="!hasItems" fill-height fluid>
        <v-layout align-center justify-center>
            <v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>
                No items yet...
                <v-btn color="primary" @click.native.prevent="emitAddItemEvent">add one</v-btn>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
  import Vue from 'vue';
  import {VueMasonryPlugin} from 'vue-masonry';

  Vue.use(VueMasonryPlugin);

  export default {
    name: "items",
    props: {
      items: {
        type: Array,
        default: []
      },
      sortable: {
        type: Boolean,
        default: false
      },
    },
    computed: {
      hasItems() {
        return this.items.length > 0;
      }
    },
    methods: {
      emitAddItemEvent() {
        this.$emit('addItem');
      }
    }
  }
</script>

<style scoped lang="scss">

    .content-element-list-item {
        /* hack for small images */
        width: 100%;
    }

    .card {
        margin: 0 10px 20px 10px;
        padding: 0;
    }

    img {
        display: block;
        width: 100%;
        height: auto;
    }

    .invalid-marker {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 2.5rem;
    }

</style>
