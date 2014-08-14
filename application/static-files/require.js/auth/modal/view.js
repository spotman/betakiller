define([
    "jquery",
    "twig",
    "text!auth/modal/template.twig"
], function($, Twig, modalTemplate) {

    return {

        show: function(successCallback, errorCallback) {

//            var template = Twig.factory(modalTemplate);
//
//            var domID = "auth-modal";

            if ( confirm("auth successful?") )
                successCallback();
            else
                errorCallback();

//            $.get("/login").done(function(content) {
//
//                var data = {
//                    domID: domID,
//                    content: content
//                };
//
//                $("body").append(template.render(data));
//
//                var modal = $("#" + domID);
//
//                modal.modal();
//
//                // TODO
//                // modal.on()
//
//            });

        }

    };

});
