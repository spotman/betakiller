/**
 * This is API wrapper file from Spotman`s kohana-simple-api
 * See more at https://github.com/spotman/kohana-simple-api
 */
define([
  "jquery",
  "jquery.jsonRPC"
], function ($, jsonRPC) {

  return function (url) {

    url = url || "/api/v1/json-rpc";

    var authLock     = false,
        requestQueue = [];

    /**
     * @param args Array
     * @param deferred {Deferred}
     */
    function addToQueue(args, deferred) {
      requestQueue.push({
        args: args,
        deferred: deferred
      });
    }

    function resolveQueue() {
      processQueue("resolve");
    }

    function rejectQueue() {
      processQueue("reject");
    }

    function processQueue(method) {
      //console.log("processing queue with [" + method + "] method");
      //console.log(requestQueue);

      // Walk through queue and resolve each deferred
      while (requestQueue.length) {
        (function (queuedRequest) {
          var queuedArgs     = queuedRequest.args,
              queuedDeferred = queuedRequest.deferred;

          //console.log(method + " queued " + queuedArgs[0] + "." + queuedArgs[0]);

          if (method === "resolve") {
            doRequest(queuedArgs)
              .done(function () {
                //console.log(method + " queued " + queuedArgs[0] + "." + queuedArgs[0] + " done!");
                queuedDeferred.resolveWith({}, arguments);
              })
              .fail(function () {
                //console.log(method + " queued " + queuedArgs[0] + "." + queuedArgs[0] + " failed!");
                queuedDeferred.rejectWith({}, arguments);
              });
          } else if (method === "reject") {
            queuedDeferred.reject();
          } else {
            throw new Error("Unknown method " + method);
          }

        }(requestQueue.shift()));
      }

      //console.log("queue processed");
      //console.log("queue length = " + requestQueue.length);
    }

    function clearQueue() {
      requestQueue = [];
      //console.log("queue cleared");
    }

    function lock() {
      authLock = true;
      //console.log("API locked");
    }

    function unlock() {
      authLock = false;
      //console.log("API unlocked");
    }

    function doRequest(requestArguments) {
      var deferred = $.Deferred();

      // Queue request when api is locked
      if (authLock) {
        addToQueue(arguments, deferred);

        return deferred;
      }

      var xhr = jsonRPC.apply({}, requestArguments);

      // Proxy successful call
      xhr.done(function () {
        deferred.resolveWith({}, arguments);
      });

      // Proxy failed call and wrap it if auth requested
      xhr.fail(function (xhr, name, JSONRPCError) {
        var code = JSONRPCError.getCode ? JSONRPCError.getCode() : null;

        if (code === 401) {
          // Lock future API requests
          lock();

          // Queue current request
          addToQueue(requestArguments, deferred);

          // Show auth modal form
          authRequiredModal();

          return;
        }

        var message = JSONRPCError ? JSONRPCError.toString() : null;

        deferred.rejectWith({}, [message]);
      });

      return deferred;
    }

    function authRequiredModal() {
      require(['auth.modal'], function(authModal) {
        var successCallback = function () {
          // Unlock API
          unlock();

          // Process all queued requests
          resolveQueue();

          // Cleanup queue
          clearQueue();
        };

        var errorCallback = function () {
          // Reject all queued request
          rejectQueue();

          // Cleanup queue
          clearQueue();

          // Allow repeat of the auth
          unlock();
        };

        // Show modal and provide callbacks
        authModal.show(successCallback, errorCallback);
      });
    }

    return function (service, method, proxyArguments) {
      var requestArguments = [url, service, method];

      proxyArguments = proxyArguments || [];

      for (var idx = 0; idx < proxyArguments.length; idx++) {
        requestArguments.push(proxyArguments[idx]);
      }

      /**
       * @link http://api.jquery.com/deferred.then/
       */
      return doRequest(requestArguments);
    }
  };

});
