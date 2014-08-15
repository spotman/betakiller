define([
    "jquery"
], function($) {

    return {

        initialize: function(successfulCallback) {

            var widget = $("#widget-auth-regular");

            var submit_button = $("#login-btn");
            var alert = widget.find(".alert");

            // Ставим курсор в поле для ввода имени пользователя
            $('#widget-auth-regular-user-login').focus();

            submit_button.click(function(e) {

                var login = $('#widget-auth-regular-user-login').val();
                var password = $('#widget-auth-regular-user-password').val();

                e.preventDefault();

                if (login == '' || password == '')
                {
                    return;
                }

                // Прячем уведомление об ошибке и выключаем кнопку
                alert.hide();
                submit_button.attr("disabled", "disabled");

                var form = $('#widget-auth-regular-login-form');

                $.JSON.post(form.data('action'), { "user-login": login, "user-password": password },
                    successfulCallback,
                    function(message)
                    {
                        // Показываем сообщение об ошибке и включаем кнопку
                        alert.html(message).show();
                        submit_button.removeAttr("disabled");
                    }
                );

            });

        }

    };

});
