<template>
    <app v-model="viewReady">
        <v-container slot="content" fill-height fluid>
            <v-layout align-center justify-center>
                <v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>
                    <EditItem
                            v-if="attributesReady"
                            :id="modelId"
                            @itemReady="itemReadyHandler"
                            @itemChanged="modelChanged = true"
                            @itemSaved="modelChanged = false"
                    />

                    <v-form v-model="isValid" v-if="definitionsReady && actualityReady">
                        <ShortcodeAttribute
                                v-for="definition in definitions"
                                v-bind="definition"
                                :key="definition.name"
                                :currentValue="getCurrentValue(definition.name)"
                                :actual="isAttributeActual(definition.name)"
                                @changed="valueChanged"
                        />

                        <v-btn color="primary" @click.prevent="insertShortcode" :disabled="buttonDisabled">
                            Insert shortcode
                        </v-btn>
                    </v-form>
                </v-flex>
            </v-layout>
        </v-container>
    </app>
</template>

<script>
  import {mapGetters} from 'vuex';
  import ApiRpc from 'content.api.rpc';
  import PostMessage from 'ckeditor-post-message';
  import App from './components/App';
  import EditItem from './components/EditItem';
  import ShortcodeAttribute from './components/ShortcodeAttribute';

  export default {
    name: "EditShortcode",
    components: {
      App,
      EditItem,
      ShortcodeAttribute
    },

    data() {
      return {
        codename: null,
        attributes: {},
        definitions: {},
        actuality: {},
        dependencies: {},

        isValid: false,
        itemReady: false,
        attributesReady: false,
        verifying: false,
        modelChanged: false,
        dependenciesReady: false
      };
    },

    computed: {
      ...mapGetters([
        'shortcodeName',
        'shortcodeTagName',
      ]),

      modelId() {
        return this.attributes.id;
      },

      viewReady() {
        //console.log(this.itemReady, this.definitionsReady, this.dependenciesReady, this.actualityReady);
        return this.itemReady && this.definitionsReady && this.dependenciesReady && this.actualityReady;
      },

      definitionsReady() {
        return Object.keys(this.definitions).length > 0;
      },

      actualityReady() {
        return Object.keys(this.actuality).length === Object.keys(this.definitions).length;
      },

      buttonDisabled() {
        return this.modelChanged || this.verifying;
      }
    },

    beforeRouteEnter(to, from, next) {
      next(vm => {
        vm.loadData();
      });
    },

    methods: {
      loadData() {
        this.attributesReady = false;
        this.definitions = {};

        this.importQueryParams();

        ApiRpc.shortcode.getAttributesDefinition(this.shortcodeName)
          .done(data => {
            this.definitions = data;
            this.collectDependencies();
            this.updateActuality();
          })
          .fail(message => {
            alert(message);
          });
      },

      importQueryParams() {
        const params = this.$route.query;

        this.attributes = [];

        for (const name in params) {
          // Somewhy vue-router converts strings to numbers
          this.attributes[name] = String(params[name]);
        }

        this.attributesReady = true;
      },

      getCurrentValue(name) {
        //console.log(name, this.attributes, this.definitions);
        return this.attributes[name] || this.definitions[name].defaultValue;
      },

      valueChanged(name, value) {
        console.log('value changed', name, value);
        this.attributes[name] = value;

        this.updateActuality();
      },

      eachDefinition(callback) {
        for (const name in this.definitions) {
          if (!this.definitions.hasOwnProperty(name)) {
            continue;
          }

          callback(name, this.definitions[name]);
        }
      },

      eachDependency(callback) {
        for (const targetName in this.dependencies) {
          if (!this.dependencies.hasOwnProperty(targetName)) {
            continue;
          }

          callback(targetName, this.dependencies[targetName]);
        }
      },

      collectDependencies() {
        this.dependenciesReady = false;

        this.eachDefinition((sourceName, definition) => {
          const attrDeps = definition.deps;

          for (const targetName in attrDeps) {
            if (attrDeps.hasOwnProperty(targetName)) {
              const targetValue = attrDeps[targetName];

              if (!this.dependencies[targetName]) {
                this.dependencies[targetName] = {};
              }

              this.dependencies[targetName][targetValue] = sourceName;
            }
          }
        });

        this.dependenciesReady = true;
      },

      updateActuality() {
        let actuality = {};

        // Calculate actual attributes and set definition flags
        this.eachDependency((targetName, valuesToName) => {
          for (var targetValue in valuesToName) {
            if (!valuesToName.hasOwnProperty(targetValue)) {
              continue;
            }

            const sourceName = valuesToName[targetValue];

            actuality[sourceName] = targetValue === this.attributes[targetName];
          }
        });

        this.eachDefinition((name) => {
          if (!actuality.hasOwnProperty(name)) {
            // Actual by default
            actuality[name] = true;
          }
        });

        this.actuality = actuality;
      },

      isAttributeActual(name) {
        return this.actuality[name];
      },

      itemReadyHandler() {
        console.log('item ready event received');
        this.itemReady = true;
      },

      insertShortcode() {
        if (this.isValid) {
          this.verifying = true;

          let data = {};

          // Filter only actual attributes
          for (const name in this.attributes) {
            if (!this.attributes.hasOwnProperty(name)) {
              continue;
            }

            if (this.isAttributeActual(name)) {
              data[name] = this.attributes[name];
            }
          }

          ApiRpc.shortcode.verify(this.shortcodeName, data)
            .done((response) => {
              PostMessage.insertShortcode(this.shortcodeTagName, response);
            })
            .fail(function (message) {
              alert(message);
            })
            .always(() => {
              this.verifying = false;
            });
        }
      }
    }
  }
</script>

<style scoped>

</style>
