import Vue from 'vue';
import Vuex from 'vuex';
import * as Mutations from './mutation-types';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    initialized: false,
    shortcodeName: null,
    shortcodeTagName: null,
    entity: {
      slug: null,
      itemId: null,
    },
    uploadEnabled: false,
    uploadUrl: null,
    acceptMimeTypes: null,
    acceptExtensions: null,
    //addAllowed: false
  },
  mutations: {
    [Mutations.INIT](state, config) {
      state.shortcodeName = config.name;
      state.shortcodeTagName = config.tagName;
      state.entity.slug = config.entitySlug;
      state.entity.itemId = config.entityItemId;
      state.addAllowed = config.addAllowed;

      state.acceptMimeTypes = config.acceptMimeTypes;
      state.acceptExtensions = config.acceptExtensions;
      state.uploadUrl = config.uploadUrl;
      if (state.uploadUrl) {
        state.uploadEnabled = true;
      }

      state.initialized = true;
      //console.log('store initialized');
    },
  },
  getters: {
    initialized: (state) => state.initialized,
    shortcodeName: (state) => state.shortcodeName,
    shortcodeTagName: (state) => state.shortcodeTagName,
    entitySlug: (state) => state.entity.slug,
    entityItemId: (state) => state.entity.itemId,
    uploadUrl: (state) => state.uploadUrl,
    uploadEnabled: (state) => state.uploadEnabled,
    acceptMimeTypes: (state) => state.acceptMimeTypes,
    acceptExtensions: (state) => state.acceptExtensions,
    //addAllowed: (state) => state.addAllowed,
  }
});
