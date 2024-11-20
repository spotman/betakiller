/**
 * This is API definition file from Spotman`s kohana-simple-api
 * See more at https://github.com/spotman/kohana-simple-api
 */
define([
    "api.rpc.factory"
], function(factory) {

    var rpc = factory(),
        phpExceptionResource = 'PhpException';

    return {
        phpException: {
          resolve: function(hash) {
              return rpc(phpExceptionResource, 'resolve', arguments);
          },

          ignore: function(hash) {
            return rpc(phpExceptionResource, 'ignore', arguments);
          },

          delete: function(hash) {
              return rpc(phpExceptionResource, 'delete', arguments);
          },

          throwHttpException: function(code) {
            return rpc(phpExceptionResource, 'throwHttpException', arguments);
          }

        }
    };

});
