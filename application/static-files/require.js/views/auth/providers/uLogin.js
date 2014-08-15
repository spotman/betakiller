define([
    "jquery"
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
                $.JSON.post(tokenLoginURL, { token: token }, widgetSuccessCallback, widgetErrorCallback);
            };

//            $.getScript("http://ulogin.ru/js/ulogin.js");
//            uLogin.init();

        }

    };

});
