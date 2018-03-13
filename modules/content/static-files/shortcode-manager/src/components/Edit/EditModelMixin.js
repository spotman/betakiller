import {mapGetters} from 'vuex';
import ApiRpc from 'content.api.rpc';

export default {

  props: {
    id: {
      type: String,
      required: true,
      default: null
    },
  },

  data() {
    return {
      modelData: {},
      formValid: false,
      saveTimeoutObject: null,
      raceTimeoutObject: null,
      savingInProgress: false
    };
  },

  computed: {
    ...mapGetters([
      'initialized',
      'shortcodeName',
    ]),

    modelDataReady () {
      return Object.keys(this.modelData).length > 0;
    },
  },

  mounted() {
    console.log('[mixin] model form mounted');
    this.loadModelData();
  },

  methods: {
    loadModelData() {
      ApiRpc.contentElement.read(this.shortcodeName, this.id)
        .done((data) => {
          console.log('edit-model data loaded', data);
          this.modelData = data;
          this.emitItemReady();
        })
        .fail((message) => {
          alert(message);
        });
    },
    modelDataChanged() {
      console.log('model data changed');
      this.emitModelChanged();

      this.throttleSaving(250, () => {
        this.saveModelData();
      });
    },
    // Save method
    saveModelData() {
      // Race condition, retry in 50ms
      if (this.savingInProgress) {
        if (!this.raceTimeoutObject) {
          this.raceTimeoutObject = setTimeout(this.saveModelData, 50);
        }

        return;
      }

      this.savingInProgress = true;
      console.log('saving model data');

      ApiRpc.contentElement.update(this.shortcodeName, this.id, this.modelData)
        .done(() => {
          console.log('model data saved');
          this.emitModelSaved();
          this.savingInProgress = false;
        })
        .fail(message => {
          alert(message);
          this.savingInProgress = false;
        })
    },
    // Throttling via setTimeout/clearTimeout
    throttleSaving(delay, callback) {
      if (this.saveTimeoutObject) {
        clearTimeout(this.saveTimeoutObject);
      }

      this.saveTimeoutObject = setTimeout(callback, delay);
    },
    emitItemReady() {
      this.$emit('itemReady');
    },
    emitModelChanged() {
      this.$emit('itemChanged');
    },
    emitModelSaved() {
      this.$emit('itemSaved');
    }
  }
}
