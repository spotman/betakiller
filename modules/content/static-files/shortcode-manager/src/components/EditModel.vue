<template>
    <v-form v-model="valid">
        <span>Edit model</span>
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

    mounted() {
      console.log('edit-model mounted');
      this.$emit('editReady');

      // TODO Load model data
    }
  }
</script>

<style scoped>

</style>
