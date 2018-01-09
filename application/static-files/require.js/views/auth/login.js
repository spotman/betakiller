define([
    "auth.widget"
], function(widget){

    return {

        initialize: function($container) {

          var redirectURL = $container.data("redirect-url");

            var readyCallback = function(){};

            var successfulCallback = function() {
                // Всё в порядке, перенаправляем пользователя
                if (redirectURL) {
                    location.href = redirectURL;
                } else {
                    location.reload(true);
                }
            };

            widget.initialize(
                $container,
                readyCallback,
                successfulCallback
            );


        }

    };

});
