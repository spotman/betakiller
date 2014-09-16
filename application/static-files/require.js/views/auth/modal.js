define([
    "jquery",
    "twig",
    "auth.widget",
    "text!templates/auth/modal.twig"
], function($, Twig, authWidget, modalTemplate) {

    return {

        /**
         *
         * @param parentSuccessCallback {Function}
         * @param parentErrorCallback {Function}
         */
        show: function(parentSuccessCallback, parentErrorCallback) {

            var template = Twig.factory(modalTemplate);

            var domID = "auth-modal",
                contentContainerID = "auth-modal-widget-container";

            var data = {
                domID: domID,
                contentContainerID: contentContainerID
            };

            $("body").append(template.render(data));

            var $modal = $("#" + domID),
                $container = $("#" + contentContainerID),
                success = false;

            var widgetReadyCallback = function() {

                console.log("auth widget is ready");

                $modal.find(".close-modal-button").click(function() {
                    success = false;
                    $modal.modal("hide");
                });

                $modal.on("hidden.bs.modal", function() {

                    if ( !success )
                        parentErrorCallback();

                    $modal.unbind().remove();
                });

                $modal.modal({
//                    backdrop: "static",
//                    keyboard: false
                });

            };

            var widgetSuccessCallback = function() {
                success = true;
                $modal.modal("hide");
                parentSuccessCallback();
            };

            authWidget.initialize(
                $container,
                widgetReadyCallback,
                widgetSuccessCallback
            );

        }

    };

});
