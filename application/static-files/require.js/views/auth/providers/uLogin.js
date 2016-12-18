define([
    "jquery",
    "jquery.utils"
], function($) {

    return {

        initialize: function(parentSuccessfulCallback) {

            var $widget = $("#widget-auth-uLogin"),
                callbackFunctionName = $widget.data("callback-function"),
                tokenLoginURL = $widget.data("token-login-url");

            var widgetSuccessCallback = function() {
                parentSuccessfulCallback();
            };

            var widgetErrorCallback = function(message) {
                message && alert(message);
            };

            // Create function in global scope
            window[callbackFunctionName] = function(token) {
                $widget.JSON(tokenLoginURL, { token: token })
                  .done(widgetSuccessCallback)
                  .fail(widgetErrorCallback);
            };

//            $.getScript("http://ulogin.ru/js/ulogin.js");
//            uLogin.init();

        }

    };

});
