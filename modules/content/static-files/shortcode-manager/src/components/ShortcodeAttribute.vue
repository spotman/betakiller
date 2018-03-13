<template>

    <!-- is hidden -->
    <input v-if="actual && hidden" type="hidden" :name="name" :value="currentValue"/>

    <v-layout v-else-if="actual && !hidden" align-center>
        <v-checkbox
                v-if="optional && isNumberType"
                v-model="enabled"
                class="shrink mr-2"
                hide-details
        />

        <!-- type "boolean" -->
        <v-switch
                v-if="isBooleanType"
                v-model="value"
                :label="label"
                :true-value="booleanTrue"
                :false-value="booleanFalse"
                :required="!optional"
                :disabled="disabled"
                hide-details
        />

        <!-- type "string" -->
        <v-text-field
                v-else-if="isStringType"
                type="text"
                v-model="value"
                :label="label"
                :required="!optional"
        />

        <!-- type "number" -->
        <v-text-field
                v-else-if="isNumberType"
                type="number"
                v-model="value"
                :label="label"
                :required="!optional"
                :disabled="disabled"
        />

        <!-- type "switch" -->
        <v-select
                v-else-if="isSwitchType"
                v-model="value"
                :label="label"
                :items="allowedValues"
                :required="!optional"
        />

        <!-- type "item" -->
        <v-select
                v-else-if="isItemType"
                v-model="value"
                :label="label"
                :items="relatedItems"
                :loading="loadingRelatedItems"
                :required="!optional"
                :search-input.sync="relatedItemsSearchTerm"
                item-avatar="imageUrl"
                item-text="label"
                item-value="id"
                :cache-items="true"
        >
            <template slot="item" slot-scope="data">
                <v-list-tile-avatar tile>
                    <img :src="data.item.imageUrl">
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title v-html="data.item.label"/>
                    <!--<v-list-tile-sub-title v-html="data.item.id"/>-->
                </v-list-tile-content>
            </template>
        </v-select>
    </v-layout>

</template>

<script>
  import {mapGetters} from 'vuex';
  import ApiRpc from 'content.api.rpc';

  export default {
    name: "shortcode-attribute",

    props: {
      type: {
        type: String,
        default: null,
        required: true
      },
      hidden: {
        type: Boolean,
        default: false,
        required: true
      },
      optional: {
        type: Boolean,
        default: false,
        required: true
      },
      actual: {
        type: Boolean,
        default: false,
        required: true
      },
      name: {
        type: String,
        default: null,
        required: true
      },
      label: {
        type: String,
        default: null,
        required: true
      },
      deps: {
        type: Object,
        default: {},
        required: true
      },
      allowedValues: {
        type: Array,
        default: []
      },
      currentValue: {
        type: String,
        default: null
      },
      defaultValue: {
        type: String,
        default: null
      },
      relatedShortcodeName: {
        type: String,
        default: null
      }
    },

    data() {
      return {
        enabled: !!this.currentValue,
        value: this.currentValue,
        booleanTrue: "true",
        booleanFalse: "false",
        relatedItems: [],
        loadingRelatedItems: false,
        relatedItemsSearchTerm: null
      };
    },

    watch: {
      enabled(val) {
        console.log('enabled changed to', val);
        this.value = val ? this.currentValue : this.defaultValue;
      },
      value() {
        this.emitChanged(String(this.value));
      },
      relatedItemsSearchTerm(val) {
        val && this.relatedItemsTermChanged(val);
      }
    },
    computed: {
      ...mapGetters([
        'entitySlug',
        'entityItemId',
      ]),

      isBooleanType() {
        return this.isAttributeType('boolean');
      },
      isNumberType() {
        return this.isAttributeType('number');
      },
      isStringType() {
        return this.isAttributeType('string');
      },
      isSwitchType() {
        return this.isAttributeType('switch');
      },
      isItemType() {
        return this.isAttributeType('item');
      },
      isRelatedItemsLoaded() {
        return this.relatedItems.length > 0;
      },
      disabled() {
        return this.optional && !this.enabled;
      }
    },

    mounted() {
      if (this.isItemType) {
        // Item IDs are integer but shortcode value is string, casting so v-select can properly detect current item
        if(this.value) {
          this.value = Number(this.value);
        }
        this.loadRelatedItems(null);
      }
    },

    methods: {
      isAttributeType(value) {
        return this.type === value;
      },

      emitChanged(value) {
        this.$emit('changed', this.name, value);
      },

      relatedItemsTermChanged(term) {
        if (this.loadingRelatedItems) {
          return;
        }

        this.loadRelatedItems(term);
      },

      loadRelatedItems(term) {
        this.loadingRelatedItems = true;

        ApiRpc.contentElement.search(this.relatedShortcodeName, this.entitySlug || null, this.entityItemId || null, term)
          .done(data => {
            this.relatedItems = data;
            this.loadingRelatedItems = false;
          })
          .fail(message => {
            alert(message);
          })
        ;
      }
    }
  }
</script>
