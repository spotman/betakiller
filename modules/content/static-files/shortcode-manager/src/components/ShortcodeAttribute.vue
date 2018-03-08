<template>

    <!-- is hidden -->
    <input v-if="actual && hidden" type="hidden" :name="name" :value="currentValue"/>

    <v-layout v-else-if="actual && !hidden" align-center>
        <v-checkbox v-if="optional && isNumberType" v-model="enabled" hide-details class="shrink mr-2"></v-checkbox>

        <!-- type "boolean" -->
        <v-switch v-if="isBooleanType" v-model="booleanValue" :label="label"
                  :required="!optional" :disabled="disabled" hide-details></v-switch>

        <!-- type "string" -->
        <v-text-field v-else-if="isStringType" type="text" v-model="stringValue" :label="label"
                      :required="!optional" :disabled="disabled"></v-text-field>

        <!-- type "number" -->
        <v-text-field v-else-if="isNumberType" type="number" v-model="numberValue" :label="label"
                      :required="!optional" :disabled="disabled"></v-text-field>

        <!-- type "switch" -->
        <v-select v-else-if="isSwitchType" v-model="switchValue" :label="label" :items="allowedValues"
                  :required="!optional" :disabled="disabled"></v-select>
    </v-layout>

</template>

<script>
  export default {
    name: "shortcode-attribute",

    props: {
      type: {
        type: String,
        default: null
      },
      hidden: {
        type: Boolean,
        default: false
      },
      optional: {
        type: Boolean,
        default: false
      },
      actual: {
        type: Boolean,
        default: false
      },
      name: {
        type: String,
        default: null
      },
      label: {
        type: String,
        default: null
      },
      deps: {
        type: Array,
        default: []
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
      }
    },

    data() {
      return {
        enabled: !!this.currentValue,
        booleanValue: (this.currentValue || this.defaultValue) === "true",
        stringValue: this.currentValue,
        numberValue: this.currentValue,
        switchValue: this.currentValue,
      };
    },

    watch: {
      enabled() {
        this.numberValue = this.stringValue = this.switchValue = this.default;
        this.booleanValue = this.defaultValue === "true";
      },
      booleanValue() {
        this.emitChanged(this.booleanValue ? "true" : "false");
      },
      numberValue() {
        this.emitChanged(String(this.numberValue));
      },
      stringValue() {
        this.emitChanged(this.stringValue);
      },
      switchValue() {
        this.emitChanged(this.switchValue);
      }
    },

    computed: {
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
      disabled() {
        return this.optional && !this.enabled;
      }
    },

    methods: {
      isAttributeType(value) {
        return this.type === value;
      },

      emitChanged(value) {
        console.log(this.name, 'value changed to', value);
        this.$emit('changed', this.name, value);
      }
    }
  }
</script>

<style scoped>

</style>
