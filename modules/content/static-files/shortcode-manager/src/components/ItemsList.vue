<template>
    <v-container v-if="hasItems" fluid>
        <v-layout v-if="selectable && sortable" row wrap class="mb-4">
            <v-flex
                    v-for="item in selectedItems"
                    :key="item.id"
                    class="mx-2 mb-3"
                    xs2 sm1>
                <img v-if="item.imageUrl" :src="item.imageUrl" :alt="item.label">
                <!-- TODO Draggable cursor + sortable behaviour -->
            </v-flex>
        </v-layout>
        <v-layout
                item-selector=".content-element-list-item"
                gutter=".content-element-list-item"
                row wrap v-masonry>
            <v-flex
                    v-for="item in items"
                    :key="item.id"
                    class="content-element-list-item"
                    v-masonry-tile xs6 sm4 md3 lg2>
                <v-card
                        :hover="true"
                        @click.native.left="clickEventHandler(item.id)"
                        @click.right.prevent.stop="contextEventHandler(item.id)"
                        class="mb-3 mx-2"
                        elevation-1>
                    <img v-if="item.imageUrl" :src="item.imageUrl" :alt="item.label">
                    <div v-else class="placeholder-icon">
                        <span>
                            <MimeTypeIcon
                                    :mimeType="item.mimeType"
                                    :default="placeholderIconName"
                                    size="6x"
                                    color="teal"
                            />
                        </span>
                    </div>
                    <v-card-title>{{ item.label }}</v-card-title>
                    <span v-if="selectable && isSelected(item.id)" class="selected-marker blue">
                        <v-icon size="2em" color="white">check</v-icon>
                    </span>
                    <span v-if="!item.isValid" class="invalid-marker">
                        <v-icon size="2em" color="red">error</v-icon>
                    </span>
                </v-card>
            </v-flex>
        </v-layout>
    </v-container>
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
  import MimeTypeIcon from './MimeTypeIcon';

  Vue.use(VueMasonryPlugin);

  export default {
    name: "ItemsList",
    components: {
      MimeTypeIcon
    },
    props: {
      items: {
        type: Array,
        required: true,
        default: []
      },
      selected: {
        type: Array,
        required: false,
        default: () => []
      },
      sortable: {
        type: Boolean,
        required: false,
        default: false
      },
      selectable: {
        type: Boolean,
        required: false,
        default: false
      },
      placeholderIconName: {
        type: String,
        default: 'file'
      }
    },
    computed: {
      hasItems() {
        return this.items.length > 0;
      },
      selectedItems() {
        return this.items.filter(item => this.isSelected(item.id));
      }
    },
    methods: {
      emitAddItemEvent() {
        this.$emit('addItem');
      },
      clickEventHandler(id) {
        if (this.selectable && this.isSelected(id)) {
          this.emitDeselectItemEvent(id);
        } else {
          this.emitSelectItemEvent(id);
        }
      },
      contextEventHandler(id) {
        this.emitContextItemEvent(id);
      },
      emitSelectItemEvent(id) {
        //console.log('selectItem event fired');
        this.$emit('selectItem', id);
      },
      emitDeselectItemEvent(id) {
        //console.log('deselectItem event fired');
        this.$emit('deselectItem', id);
      },
      emitContextItemEvent(id) {
        //console.log('contextItem event fired');
        this.$emit('contextItem', id);
      },
      isSelected(id) {
        return this.selected.indexOf(id) !== -1;
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
        /*margin: 0 10px 20px 10px;*/
        padding: 0;
    }

    .card--content {
        position: relative;
    }

    img {
        display: block;
        width: 100%;
        height: auto;
    }

    .invalid-marker {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
    }

    .selected-marker {
        position: absolute;
        top: -10px;
        right: -10px;
        z-index: 2;
        background: white;
        max-width: 2em;
        max-height: 2em;
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

    .placeholder-icon span {
        display: block;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -3em;
        margin-left: -2em;
        z-index: 2;
    }

</style>
