
export default {
  data() {
    return {
      itemReady: false,
      itemChanged: false,
    };
  },

  methods: {

    itemReadyHandler() {
      //console.log('item ready event received');
      this.itemReady = true;
    },

    itemChangedHandler() {
      this.itemChanged = true;
    },

    itemSavedHandler() {
      this.itemChanged = false;
    }

  }

}
