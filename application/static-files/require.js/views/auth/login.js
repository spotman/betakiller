define([
    "auth.widget"
], function(widget){

    return {

        initialize: function($container, redirectURL) {

            var readyCallback = function(){};

            var successfulCallback = function()
            {
                // Всё в порядке, перенаправляем пользователя
                location.href = redirectURL;
            };

            widget.initialize(
                $container,
                readyCallback,
                successfulCallback
            );


        }

    };

});
