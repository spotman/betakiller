<template>
    <app v-model="itemReady">
        <v-container slot="content" fill-height fluid>

            <EditItem
                    :id="modelId"
                    @itemReady="itemReadyHandler"
                    @itemChanged="itemChangedHandler"
                    @itemSaved="itemSavedHandler"
            />

        </v-container>
    </app>
</template>

<script>
  import App from './components/App';
  import EditItem from './components/EditItem';
  import EditViewMixin from './components/Edit/EditViewMixin';
  export default {
    name: "EditItemView",
    components: {
      App,
      EditItem
    },
    mixins: [
      EditViewMixin
    ],

    computed: {
      modelId() {
        return Number(this.$route.params.id);
      }
    },

    //beforeRouteEnter(to, from, next) {
    //  next(vm => {
    //    vm.loadData();
    //  });
    //},
    //
    //methods: {
    //  loadData() {
    //  },
    //}
  }
</script>
