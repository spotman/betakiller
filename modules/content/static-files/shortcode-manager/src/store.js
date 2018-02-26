import Vue from 'vue';
import Vuex from 'vuex';
import * as Mutations from './mutation-types';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    initialized: false,
    shortcodeName: null,
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
    },
  },
  getters: {
    initialized: (state) => state.initialized,
    shortcodeName: (state) => state.shortcodeName,
    entitySlug: (state) => state.entity.slug,
    entityItemId: (state) => state.entity.itemId,
    uploadUrl: (state) => state.uploadUrl,
    uploadEnabled: (state) => state.uploadEnabled,
    acceptMimeTypes: (state) => state.acceptMimeTypes,
    acceptExtensions: (state) => state.acceptExtensions,
    //addAllowed: (state) => state.addAllowed,
  }
});
