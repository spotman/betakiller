<template>
    <!-- Display different views for different shortcodes -->
    <component
            v-if="initialized"
            :is="componentView"
            :id="id"
            v-on="$listeners"
    />
</template>

<script>
  import {mapGetters} from 'vuex';
  import EditGallery from './Edit/EditGallery';
  import EditImage from './Edit/EditImage';
  import EditAttachment from './Edit/EditAttachment';
  import EditYoutube from './Edit/EditYoutube';

  export default {
    name: "EditItem",
    props: {
      id: {
        type: Number,
        required: true,
        default: null
      },
    },
    computed: {
      ...mapGetters([
        'initialized',
        'shortcodeName',
      ]),

      componentView() {
        const name = this.shortcodeName;

        switch (name.toLowerCase()) {
          case 'gallery':
            return EditGallery;

          case 'image':
            return EditImage;

          case 'attachment':
            return EditAttachment;

          case 'youtube':
            return EditYoutube;

          default:
            throw 'Unknown shortcode name ' + name;
        }
      },
    }
  }
</script>
