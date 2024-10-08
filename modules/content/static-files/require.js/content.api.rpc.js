/**
 * This is API definition file from Spotman`s kohana-simple-api
 * See more at https://github.com/spotman/kohana-simple-api
 */
define([
  "api.rpc.factory"
], function (factory) {

  var rpc                    = factory(),
      postResource           = 'ContentPost',
      commentResource        = 'ContentComment',
      contentElementResource = 'ContentElement',
      shortcodeResource      = 'Shortcode';

  return {
    post: {
      complete: function (id) {
        return rpc(postResource, 'complete', arguments);
      },

      publish: function (id) {
        return rpc(postResource, 'publish', arguments);
      },

      reject: function (id) {
        return rpc(postResource, 'reject', arguments);
      },

      fix: function (id) {
        return rpc(postResource, 'fix', arguments);
      },

      pause: function (id) {
        return rpc(postResource, 'pause', arguments);
      },

      update: function (id, data) {
        return rpc(postResource, 'update', arguments);
      }
    },

    comment: {
      approve: function (id) {
        return rpc(commentResource, 'approve', arguments);
      },

      reject: function (id) {
        return rpc(commentResource, 'reject', arguments);
      },

      markAsSpam: function (id) {
        return rpc(commentResource, 'markAsSpam', arguments);
      },

      moveToTrash: function (id) {
        return rpc(commentResource, 'moveToTrash', arguments);
      },

      restoreFromTrash: function (id) {
        return rpc(commentResource, 'restoreFromTrash', arguments);
      },

      create: function (data) {
        return rpc(commentResource, 'create', arguments);
      },

      update: function (data) {
        return rpc(commentResource, 'update', arguments);
      }
    },

    shortcode: {
      getAttributesDefinition(name) {
        return rpc(shortcodeResource, 'getAttributesDefinition', arguments);
      },

      verify: function (name, attributes) {
        return rpc(shortcodeResource, 'verify', arguments);
      },
    },

    contentElement: {
      read(name, id) {
        return rpc(contentElementResource, 'read', arguments);
      },
      update(name, id, data) {
        return rpc(contentElementResource, 'update', arguments);
      },
      list (name, entitySlug, entityItemID) {
        return rpc(contentElementResource, 'list', arguments);
      },
      search (name, entitySlug, entityItemID, term) {
        return rpc(contentElementResource, 'search', arguments);
      },
    }
  };

});
