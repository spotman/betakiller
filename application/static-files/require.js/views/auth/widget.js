define([
    "jquery"
], function($) {

    return {

        /**
         *
         * @param $parentContainer {*}
         * @param readyCallback {Function}
         * @param successCallback {Function}
         */
        initialize: function($parentContainer, readyCallback, successCallback) {

            var init = function() {

                var $widget = $(".widget-auth"),
                    providers = $widget.data("providers").split(","),
                    requiredAuthProviders = [];

                // Create list of required modules
                $.each(providers, function(index, name) {
                    requiredAuthProviders.push("auth.provider." + name);
                });

                var providerSuccessfulCallback = function() {
                    successCallback();
                };

                require(requiredAuthProviders, function() {

                    // Initializing JS for each module
                    $.each(arguments, function(index, authProviderModule) {
                        authProviderModule.initialize(providerSuccessfulCallback);
                    });

                    // Notify parent
                    readyCallback();
                });

            };

            // Если внутри контейнера уже есть разметка виджета, то сразу инициализируем
            if ( $parentContainer.html() ) {
                init();
            } else {
              // Иначе сперва загружаем разметку и внедряем её в контейнер
                require(["text!/w/Auth"], function(widgetHTML) {

                    // Rendering whole widget
                    $parentContainer.empty().append(widgetHTML);

                    init();
                });
            }

        }

    };

});
