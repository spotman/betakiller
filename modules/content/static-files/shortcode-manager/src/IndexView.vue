<template>
    <app v-model="appReady">
        <v-toolbar slot="toolbar" app>
            <v-switch v-model="showGlobal" label="Show global items" hide-details></v-switch>
            <v-spacer></v-spacer>
            <v-text-field prepend-icon="search" solo label="Search" hide-details></v-text-field>
        </v-toolbar>

        <v-container slot="content" fill-height fluid>
            <items v-if="itemsLoaded" :items="items"
                   @addItem="addItem" @selectItem="selectItem" @contextItem="contextItem"></items>

            <file-upload v-if="uploadEnabled" v-model="files" :post-action="uploadUrl" :multiple="true"
                         :thread="3" :drop="true" :drop-directory="true" ref="upload" @input-file="inputFile"
                         :data="uploadData" :accept="acceptMimeTypes" :extensions="acceptExtensions"></file-upload>

            <!--<v-snackbar color="success" :timeout="3000" v-model="verified">-->
            <!--Verified successfully-->
            <!--<v-btn flat color="black" @click.native="verified = false">Close</v-btn>-->
            <!--</v-snackbar>-->
        </v-container>
    </app>
</template>

<script>
  import contentRpc from 'content.api.rpc';
  import App from './components/App';
  import Items from './components/Items';
  import {mapGetters} from 'vuex';
  import FileUpload from 'vue-upload-component';

  export default {
    name: "index",

    components: {
      App,
      Items,
      FileUpload
    },

    data() {
      return {
        appReady: false,
        showGlobal: false,

        itemsLoaded: false,
        items: [],
        files: []
      }
    },

    computed: {

      ...mapGetters([
        'initialized',
        'uploadEnabled',
        'uploadUrl',
        'acceptMimeTypes',
        'acceptExtensions',
        'shortcodeName',
        'entitySlug',
        'entityItemId',
      ]),

      uploadData() {
        return {
          entitySlug: this.entitySlug,
          entityItemID: this.entityItemId
        };
      }
    },

    watch: {
      showGlobal() {
        this.fetchData();
      },
      itemsLoaded() {
        this.$nextTick(() => {
          console.log('all rendered');
          this.appReady = true;
        })
      },
      files() {
        console.log('this.files changed');
        console.log(this.files);
      }
    },

    beforeRouteEnter (to, from, next) {
      next(vm => {
        vm.fetchData();
      })
    },

    methods: {
      addItem() {
        if (this.uploadEnabled) {
          // Open file upload OS dialog via clicking to the <input type=file> element
          this.$refs.upload.$el.click();
        } else {
          // Show add item dialog (call route /add)
          this.$router.push('add');
        }
      },

      selectItem(id) {
        // Show edit shortcode dialog (call route /edit/shortcode/?id=:id)
        this.$router.push({name: 'edit-shortcode', query: {id}, exact: true});
      },

      contextItem(id) {
        // Show edit item dialog (call route /edit/:id)
        this.$router.push({name: 'edit-item', params: {id}, exact: true});
      },

      fetchData() {
        this.itemsLoaded = false;

        const promise = this.showGlobal
          ? contentRpc.contentElement.list(this.shortcodeName, null, null)
          : contentRpc.contentElement.list(this.shortcodeName, this.entitySlug, this.entityItemId);

        promise
          .done((data) => {
            this.items = data;
            this.itemsLoaded = true;
          })
          .fail((message) => {
            // TODO Error message
            alert(message || "Error!");
          })
      },

      // add, update, remove File Event
      inputFile(newFile, oldFile) {
        if (newFile && oldFile) {
          // update
          if (newFile.active && !oldFile.active) {
            // beforeSend
            // min size
            //if (newFile.size >= 0 && this.minSize > 0 && newFile.size < this.minSize) {
            //  this.$refs.upload.update(newFile, { error: 'size' })
            //}
          }
          if (newFile.progress !== oldFile.progress) {
            // TODO progress bar
          }
          if (newFile.error && !oldFile.error) {
            // TODO error
          }
          if (newFile.success && !oldFile.success) {
            const response = newFile.response;
            if (response && response.response === "ok") {
              // TODO success (add)
              console.log(response.message);
            } else {
              // TODO Error
            }
          }
        }
        //if (!newFile && oldFile) {
        //  // remove
        //  if (oldFile.success && oldFile.response.id) {
        //    // $.ajax({
        //    //   type: 'DELETE',
        //    //   url: '/upload/delete?id=' + oldFile.response.id,
        //    // })
        //  }
        //}
        // Automatically activate upload
        if (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
          if (!this.$refs.upload.active) {
            this.$refs.upload.active = true;
          }
        }
      }
    }
  }
</script>

<style scoped>

    .file-uploads {
        display: none;
    }

</style>
