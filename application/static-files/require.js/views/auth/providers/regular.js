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
                $submitButton = $form.find('button[type="submit"]'),
                $alert = $widget.find(".alert");

            // Ставим курсор в поле для ввода имени пользователя
            $login.focus();

            $form.submit(function(e) {
                var login = $login.val(),
                    password = $pass.val();

                e.preventDefault();

                if (login === '' || password === '') {
                  return;
                }

                // Прячем уведомление об ошибке и выключаем кнопку
                $alert.hide();
                $submitButton.attr("disabled", "disabled");

                $form.JSON($form.data('action'), {
                    "user-login": login,
                    "user-password": password
                })
                  .done(function() {
                    successfulCallback();
                  })
                  .fail(function(message) {
                    // Показываем сообщение об ошибке и включаем кнопку
                    $alert.html(message).removeClass("hide").show();
                    console.log(message || "error");
                    $submitButton.removeAttr("disabled");
                  });
            });

        }

    };

});
