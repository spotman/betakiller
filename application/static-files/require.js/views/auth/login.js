define([
    "auth.widget"
], function(widget){

    return {

        initialize: function($container, redirectURL) {

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
