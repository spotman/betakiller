import Vue from 'vue';
import Vuetify from 'vuetify';
import Router from './router';
import Store from './store';
import * as Mutations from './mutation-types';
import 'vuetify/dist/vuetify.min.css'; // Ensure you are using css-loader

Vue.use(Vuetify);

new Vue({
  el: '#tags-manager',
  router: Router,
  store: Store,

  mounted: function () {
    console.log('root mounted');
    this.$store.commit(Mutations.INIT, this.$el.dataset);
  },
})
