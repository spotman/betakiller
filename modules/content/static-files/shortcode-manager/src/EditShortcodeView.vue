<template>
    <v-app id="edit-shortcode-view">
        <v-content>
            <!--v-if="dataLoaded" -->
            <v-container fill-height fluid>
                <v-layout align-center justify-center>
                    <v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>
                        <span>edit shortcode {{ codename }}</span>
                        <edit-model></edit-model>

                        <v-form v-model="valid">
                            <shortcode-attribute v-for="definition in definitions" v-bind="definition"
                                                 :key="definition.name" :currentValue="getCurrentValue(definition.name)"
                                                 @changed="valueChanged"></shortcode-attribute>
                        </v-form>
                    </v-flex>
                </v-layout>
                <!--<v-layout align-center justify-center>-->
                    <!--<v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>-->
                    <!--</v-flex>-->
                <!--</v-layout>-->
            </v-container>

            <!--<v-container v-else-if="!dataLoaded" fill-height fluid>-->
            <!--<v-layout align-center justify-center>-->
            <!--<v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>-->
            <!--<v-progress-circular indeterminate color="primary"></v-progress-circular>-->
            <!--</v-flex>-->
            <!--</v-layout>-->
            <!--</v-container>-->
        </v-content>
    </v-app>
</template>

<script>
  import {mapGetters} from 'vuex';
  import ApiRpc from 'content.api.rpc';
  import EditModel from './components/EditModel';
  import ShortcodeAttribute from './components/ShortcodeAttribute';

  export default {
    name: "edit-shortcode",
    components: {
      EditModel,
      ShortcodeAttribute
    },

    data() {
      return {
        codename: null,
        attributes: {},
        definitions: {},
        modelReady: false,
        valid: false
      };
    },

    computed: {
      ...mapGetters([
        'shortcodeName',
      ]),

      dataLoaded() {
        return this.definitions.length > 0;
      }
    },

    beforeRouteEnter(to, from, next) {
      next(vm => {
        vm.loadData();
      });
    },

    methods: {
      loadData() {
        console.log('load shortcode data');

        this.attributes = this.$route.query;

        ApiRpc.shortcode.getAttributesDefinition(this.shortcodeName)
          .done(data => {
            this.definitions = data;
            console.log('shortcode data loaded');
            console.log(this);
          })
          .fail(message => {
            // TODO Error message
          });
      },

      getCurrentValue(name) {
        console.log(name, this.attributes, this.definitions);
        return this.attributes[name] || this.definitions[name].defaultValue;
      },

      valueChanged(name, value) {
        console.log(name, value);
        this.attributes[name] = value;
      }
    }
  }
</script>

<style scoped>

</style>
