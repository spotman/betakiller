/**
 * This is API definition file from Spotman`s kohana-simple-api
 * See more at https://github.com/spotman/kohana-simple-api
 */
define([
    "api.rpc.factory"
], function(factory) {

    var rpc = factory(),
        postResource = 'ContentPost',
        commentResource = 'ContentComment';

    return {
        post: {
          publish: function(id) {
              return rpc(postResource, 'publish', arguments);
          },

          save: function(data) {
              return rpc(postResource, 'save', arguments);
          }
        },

        comment: {
          approve: function(id) {
              return rpc(commentResource, 'approve', arguments);
          },

          reject: function(id) {
              return rpc(commentResource, 'reject', arguments);
          },

          markAsSpam: function(id) {
              return rpc(commentResource, 'markAsSpam', arguments);
          },

          moveToTrash: function(id) {
              return rpc(commentResource, 'moveToTrash', arguments);
          },

          restoreFromTrash: function(id) {
              return rpc(commentResource, 'restoreFromTrash', arguments);
          },

          create: function(data) {
            return rpc(commentResource, 'create', arguments);
          },

          update: function(data) {
              return rpc(commentResource, 'update', arguments);
          }
        }
    };

});
