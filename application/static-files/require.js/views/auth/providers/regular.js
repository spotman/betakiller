define([
    "jquery",
    "jquery.utils"
], function($) {

    return {

        initialize: function(successfulCallback) {

            var widget = $("#widget-auth-regular"),
                form = $('#widget-auth-regular-login-form'),
                submit_button = $("#login-btn"),
                alert = widget.find(".alert");

            // Ставим курсор в поле для ввода имени пользователя
            $('#widget-auth-regular-user-login').focus();

            var providerDoneCallback = function() {
                successfulCallback();
            };

            var providerFailCallback = function(message) {
                // Показываем сообщение об ошибке и включаем кнопку
                alert.html(message).show();
                submit_button.removeAttr("disabled");
            };

            form.submit(function(e) {

                var login = $('#widget-auth-regular-user-login').val();
                var password = $('#widget-auth-regular-user-password').val();

                e.preventDefault();

                if (login == '' || password == '')
                    return;

                // Прячем уведомление об ошибке и выключаем кнопку
                alert.hide();
                submit_button.attr("disabled", "disabled");

                $.JSON.post(
                    form.data('action'),
                    {
                        "user-login": login,
                        "user-password": password
                    },
                    providerDoneCallback,
                    providerFailCallback
                );

            });

        }

    };

});
