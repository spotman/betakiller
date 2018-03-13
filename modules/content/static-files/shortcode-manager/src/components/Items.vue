<template>
    <!--<v-container >-->
        <v-layout v-if="hasItems" row wrap v-masonry item-selector=".content-element-list-item" gutter=".content-element-list-item">
            <v-flex xs6 sm4 md3 lg2 v-for="item in items" class="content-element-list-item" :key="item.id"
                    v-masonry-tile>
                <v-card elevation-1 @click.native.left="emitSelectItemEvent(item.id)" :hover="true"
                        @click.right.prevent.stop="emitContextItemEvent(item.id)">
                    <v-tooltip bottom>
                        <img v-if="item.imageUrl" :src="item.imageUrl" alt="" slot="activator">
                        <div v-else slot="activator" class="placeholder-icon">
                            <v-icon size="80px" color="teal">{{ placeholderIconName }}</v-icon>
                        </div>
                        <span>{{ item.label }}</span>
                    </v-tooltip>
                    <span v-if="!item.isValid" class="invalid-marker material-icons red--text">error</span>
                </v-card>
            </v-flex>
        </v-layout>
    <!--</v-container>-->
    <v-container v-else-if="!hasItems" fill-height fluid>
        <v-layout align-center justify-center>
            <v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>
                No items yet...
                <v-btn color="primary" @click="emitAddItemEvent">add one</v-btn>
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
      placeholderIconName: {
        type: String,
        default: 'image'
      }
    },
    computed: {
      hasItems() {
        return this.items.length > 0;
      }
    },
    methods: {
      emitAddItemEvent() {
        this.$emit('addItem');
      },
      emitSelectItemEvent(id) {
        console.log('selectItem event fired');
        this.$emit('selectItem', id);
      },
      emitContextItemEvent(id) {
        console.log('contextItem event fired');
        this.$emit('contextItem', id);
      }
    }
  }
</script>

<style scoped lang="scss">

    .content-element-list-item {
        /* hack for small images */
        width: 100%;
        cursor: pointer;
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
        right: 10px;
        font-size: 2.5rem;
    }

    .placeholder-icon {
        width: 100%;
        position: relative;
        text-align: center;
    }

    .placeholder-icon:before {
        display: block;
        position: relative;
        content: "";
        width: 100%;
        padding-top: 100%;
        z-index: 1;
    }

    .placeholder-icon .material-icons {
        display: block;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -40px;
        margin-left: -40px;
        z-index: 2;
    }

</style>
