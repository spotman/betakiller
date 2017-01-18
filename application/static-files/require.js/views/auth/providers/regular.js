define([
    "jquery",
    "jquery.utils"
], function($) {

    return {

        initialize: function(successfulCallback) {

            var $widget = $(".widget-auth-regular"),
                $form = $widget.find('form[name="regular-login-form"]'),
                $login = $form.find('input[name="user-login"]'),
                $pass = $form.find('input[name="user-password"]'),
                $remember = $form.find('input[name="remember"]'),
                $submitButton = $form.find('button[type="submit"]'),
                $alert = $widget.find(".alert");

            // Ставим курсор в поле для ввода имени пользователя
            $login.focus();

            var providerDoneCallback = function() {
                successfulCallback();
            };

            var providerFailCallback = function(message) {
                // Показываем сообщение об ошибке и включаем кнопку
                $alert.html(message).removeClass("hide").show();
                console.log(message || "error");
                $submitButton.removeAttr("disabled");
            };

            $form.submit(function(e) {
                var login = $login.val(),
                    password = $pass.val(),
                    remember = $remember.is(":checked");

                e.preventDefault();

                if (login == '' || password == '')
                    return;

                // Прячем уведомление об ошибке и выключаем кнопку
                $alert.hide();
                $submitButton.attr("disabled", "disabled");

                $form.JSON($form.data('action'), {
                    "user-login": login,
                    "user-password": password,
                    "remember": remember ? 1 : 0
                }).done(providerDoneCallback).fail(providerFailCallback);
            });

        }

    };

});
