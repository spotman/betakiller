<template>
    <v-form v-if="modelDataReady" v-model="formValid">
        <v-card class="mb-4">
            <v-card-media :src="getPreviewUrl" height="350px"/>
        </v-card>
        <v-text-field
                v-model="modelData.youtubeID"
                label="YouTube ID"
                @input="inputHandler"
                prefix="https://www.youtube.com/watch?v="
                required
        />
    </v-form>
</template>

<script>
  import EditModelMixin from "./EditModelMixin";

  export default {
    name: "EditYoutube",
    mixins: [
      EditModelMixin
    ],

    computed: {
      getPreviewUrl() {
        return 'https://img.youtube.com/vi/' + this.modelData.youtubeID + '/0.jpg'
      },
    },

    methods: {
      inputHandler(val) {
        const prefix = 'https://www.youtube.com/watch?v=';
        if (val.includes(prefix)) {
          this.modelData.youtubeID = val.split(prefix)[1];
        }

        this.modelDataChanged();
      }
    }
  }
</script>
