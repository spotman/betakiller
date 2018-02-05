/**
 * This is API definition file from Spotman`s kohana-simple-api
 * See more at https://github.com/spotman/kohana-simple-api
 */
define([
  "api.rpc.factory"
], function (factory) {

  const rpc                = factory(),
        missingUrlResource = 'MissingUrl';

  return {
    missingUrl: {
      delete: function (id) {
        return rpc(missingUrlResource, 'delete', arguments);
      },

      update: function (data) {
        return rpc(missingUrlResource, 'update', arguments);
      }
    },
  };

});
