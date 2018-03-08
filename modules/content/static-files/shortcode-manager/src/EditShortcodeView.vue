<template>
    <app v-model="viewReady">
        <v-container slot="content" fill-height fluid>
            <v-layout align-center justify-center>
                <v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>
                    <edit :id="modelId" @editReady="editReadyHandler"></edit>

                    <v-form v-model="isValid">
                        <shortcode-attribute v-for="definition in definitions" v-bind="definition"
                                             :key="definition.name" :currentValue="getCurrentValue(definition.name)"
                                             @changed="valueChanged"
                                             :actual="actuality[definition.name]"></shortcode-attribute>

                        <v-btn color="primary" @click.prevent="insertShortcode" :disabled="verifying">
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
  import Edit from './components/Edit';
  import ShortcodeAttribute from './components/ShortcodeAttribute';

  export default {
    name: "edit-shortcode",
    components: {
      App,
      Edit,
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
        editReady: false,
        verifying: false
      };
    },

    computed: {
      ...mapGetters([
        'shortcodeName',
      ]),

      modelId() {
        return this.attributes.id;
      },

      viewReady() {
        return this.editReady && Object.keys(this.definitions).length > 0;
      }
    },

    beforeRouteEnter(to, from, next) {
      next(vm => {
        vm.loadData();
      });
    },

    methods: {
      loadData() {
        this.importQueryParams();

        ApiRpc.shortcode.getAttributesDefinition(this.shortcodeName)
          .done(data => {
            this.definitions = data;
            this.collectDependencies();
            this.updateActuality();
          })
          .fail(message => {
            // TODO Error message
          });
      },

      importQueryParams() {
        const params = this.$route.query;

        this.attributes = [];

        for (const name in params) {
          // Somewhy vue-router converts strings to numbers
          this.attributes[name] = String(params[name]);
        }
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
      },

      updateActuality() {
        // Calculate actual attributes and set definition flags
        this.eachDependency((targetName, valuesToName) => {
          for (var targetValue in valuesToName) {
            if (!valuesToName.hasOwnProperty(targetValue)) {
              continue;
            }

            const sourceName = valuesToName[targetValue];

            this.actuality[sourceName] = targetValue === this.attributes[targetName];
          }
        });

        this.eachDefinition((name) => {
          if (!this.actuality.hasOwnProperty(name)) {
            // Actual by default
            this.actuality[name] = true;
          }
        });
      },

      editReadyHandler() {
        console.log('edit ready event received');
        this.editReady = true;
      },

      insertShortcode() {
        if (this.isValid) {
          this.verifying = true;

          // Cast observer to plain object
          const data = {...this.attributes};

          ApiRpc.shortcode.verify(this.shortcodeName, data)
            .done((response) => {
              PostMessage.insertShortcode(this.shortcodeName, response);
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
