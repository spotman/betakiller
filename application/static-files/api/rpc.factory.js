/**
 * This is API wrapper file from Spotman`s kohana-simple-api
 * See more at https://github.com/spotman/kohana-simple-api
 */
define([
    "jquery",
    "jquery.jsonRPC",
    "auth.modal"
], function($, jsonRPC, authModal) {

    return function(url) {

        url = url || "/api/v1/json-rpc";

        var authLock = false,
            requestQueue = [];

        /**
         * @param args Array
         * @returns {Deferred}
         */
        function addToQueue(args) {

            var fullMethod = args[1] + "." + args[2];

            var deferred = $.Deferred()
                .done(function() {
                    console.log(fullMethod + " processed from queue");
                    return doRequest(args);
                });

            requestQueue.push(deferred);

            console.log(fullMethod + " is queued");

            return deferred;
        }

        function processQueue() {

            console.log("processing queue:");
            console.log(requestQueue);

            // Walk through queue and resolve each deferred
            while (requestQueue.length)
            {
                requestQueue.shift().resolve();
            }

//            $.each(requestQueue, function(index, deferred) {
//                console.log(arguments);
//                deferred.resolve();
//            });

            // Cleanup queue
            clearQueue();

            console.log("queue processed");
            console.log("queue length = " + requestQueue.length);
        }

        function clearQueue() {
            requestQueue = [];
        }

        function lock() {
            authLock = true;
            console.log("API locked");
        }

        function unlock() {
            authLock = false;
            console.log("API unlocked");
        }

        function doRequest(requestArguments) {

            // Queue request when api is locked
            if ( authLock )
                return addToQueue(arguments);

            return jsonRPC.apply({}, requestArguments)

                // Overwrite original Deferred with queue callback
                .then(null, function(xhr, name, JSONRPCError) {
                    var code = JSONRPCError.getCode();

                    if (code == 401)
                    {
                        // Lock future API requests
                        lock();

                        // Queue current request and return new JQuery.Deferred
                        var newPromise = addToQueue(requestArguments);

                        // Show auth modal form
                        authRequiredModal();

                        return newPromise;
                    }

                    return null;
                });
        }

        function authRequiredModal() {

            var successCallback = function() {
                // Unlock API
                unlock();

                // Process all queued requests
                processQueue();
            };

            var errorCallback = function() {
                // Allow repeat of the auth
                unlock();
            };

            // Show modal and provide callbacks
            authModal.show(successCallback, errorCallback);
        }

        return function (service, method, proxyArguments) {

            var requestArguments = [url, service, method];

            proxyArguments = proxyArguments || [];

            for (var idx = 0; idx < proxyArguments.length; idx++)
                requestArguments.push(proxyArguments[idx]);

            /**
             * @link http://api.jquery.com/deferred.then/
             */
            return doRequest(requestArguments);
        }
    };

});
