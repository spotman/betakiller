import Vue from 'vue'
import App from './App.vue'

new Vue({
  el: '#tags-manager',

  props: {
    entitySlug: {
      type: String,
      default: null
    },

    entityItemID: {
      type: Number,
      default: null
    },
  },

  propsData: {
    entitySlug: 'post',
    entityItemID: 234
  },

  mounted: function () {
    //console.log("attributes are  ", this.$el.dataset);
    //this.entitySlug = this.$el.attributes.myData.value;
  },

  render: h => h(App)
})
