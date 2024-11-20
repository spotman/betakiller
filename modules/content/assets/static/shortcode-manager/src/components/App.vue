<template>
    <v-app v-if="initialized">
        <slot v-show="appReady" name="toolbar"></slot>

        <v-content v-show="appReady">
            <slot name="content"></slot>
            <slot name="hidden" v-show="false"></slot>
        </v-content>

        <v-content v-show="!appReady">
            <v-container fill-height fluid>
                <v-layout align-center justify-center>
                    <v-flex xs12 sm8 md6 lg4 xl3 text-xs-center>
                        <v-progress-circular indeterminate color="primary"></v-progress-circular>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
  import {mapGetters} from 'vuex';
  export default {
    name: "loader",

    model: {
      prop: 'appReady',
      event: 'appReady'
    },

    props: {
      appReady: {
        type: Boolean,
        default: false
      }
    },

    computed: {
      ...mapGetters([
        'initialized',
      ])
    }
  }
</script>

<style scoped>

</style>
