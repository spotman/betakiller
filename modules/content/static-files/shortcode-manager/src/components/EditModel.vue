<template>
    <v-form v-model="valid">
        <span>Edit model</span>
        <!-- TODO Display different views for different shortcodes -->

    </v-form>
</template>

<script>
  export default {
    name: "edit-model",

    data() {
      return {
        valid: false
      };
    },

    created() {
      console.log('edit-model created');
      this.$emit('modelReady');
      console.log('modelReady emitted');

      // TODO Load model data
    }
  }
</script>

<style scoped>

</style>
