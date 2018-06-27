<template>
    <ItemsList
            v-if="itemsLoaded"
            :items="items"
            :selected="selected"
            :sortable="sortable"
            :selectable="selectable"
            :placeholderIconName="getDefaultIcon()"
            v-on="$listeners"
    />
</template>

<script>
  import contentRpc from 'content.api.rpc';
  import ItemsList from './ItemsList';
  import {mapGetters} from 'vuex';

  export default {
    name: "ItemsIndex",
    props: {
      shortcodeName: {
        type: String,
        required: true
      },
      showGlobal: {
        type: Boolean,
        required: true
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
    },
    data() {
      return {
        itemsLoaded: false,
        items: [],
        defaultFaIcons: {
          Image: 'image',
          Attachment: 'file',
          Gallery: 'images',
          Youtube: 'youtube'
        }

      };
    },
    computed: {
      ...mapGetters([
        'entitySlug',
        'entityItemId',
      ]),

    },
    components: {
      ItemsList,
    },

    watch: {
      selected(val) {
        console.log('selected items changed', val);
      },
      showGlobal() {
        this.fetchData();
      },
      itemsLoaded(val) {
        if(val === true) {
          this.$nextTick(() => {
            console.log('ItemsIndex itemsLoaded');
            this.emitItemsReadyEvent();
          })
        }
      }
    },

    mounted() {
      console.log('ItemsIndex mounted');
      this.fetchData();
    },

    methods: {
      getDefaultIcon() {
        return this.defaultFaIcons[this.shortcodeName];
      },

      fetchData() {
        this.itemsLoaded = false;

        const promise = this.showGlobal
          ? contentRpc.contentElement.list(this.shortcodeName, null, null)
          : contentRpc.contentElement.list(this.shortcodeName, this.entitySlug, this.entityItemId);

        promise
          .done((data) => {
            this.items = data;
            this.itemsLoaded = true;
          })
          .fail((message) => {
            // TODO Error message
            alert(message || "Error!");
          })
      },

      emitItemsReadyEvent() {
        console.log('itemsReady event fired');
        this.$emit('itemsReady');
      },
    }
    // TODO Methods for loading items from server
  }
</script>

<style scoped>

</style>
