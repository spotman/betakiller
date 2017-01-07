define([
    "jquery",
    "jquery.utils"
], function($) {

    return {

        initialize: function(parentSuccessfulCallback) {

            var $widget = $("#widget-auth-uLogin"),
                callbackFunctionName = $widget.data("callback-function"),
                tokenLoginURL = $widget.data("token-login-url");

            // Create function in global scope
            window[callbackFunctionName] = function(token) {
                $widget.JSON(tokenLoginURL, { token: token })
                  .done(function() {
                    parentSuccessfulCallback();
                  })
                  .fail(function(message) {
                    message && alert(message);
                  });
            };

        }

    };

});
