<? defined('SYSPATH') OR die('No direct script access.') ?>

<style type="text/css">#login-window { margin-top: 150px; }</style>

<div class="container">
    <div class="row">
        <div id="login-window" class="span4 offset4 well">
            <legend><?= __('Sign in') ?></legend>
            <div class="alert alert-error hide"></div>
            <form method="POST" action="/login/" id="login-form" accept-charset="UTF-8">
                <input type="text" id="user-login" name="user-login" class="span4" placeholder="<?= __("Your username or email") ?>" value="<?= $username ?>" />
                <input type="password" id="user-password" name="user-password" class="span4" placeholder="<?= __("Your password")?>" />
                <button id="login-btn" class="btn btn-info btn-block"><i class="icon-user icon-white"></i> <?= __("Sign in") ?></button>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">

    $(function()
    {
        // Позиционирование окошка строго посередине
        var window_height = $(window).height();
        var login_window = $("#login-window");
        var login_height = login_window.outerHeight();

        login_window.css({ "margin-top": ( (window_height - login_height) / 2 ) });

        var submit_button = $("#login-btn");
        var alert = login_window.find(".alert");

        // Ставим курсор в поле для ввода имени пользователя
        $('#user-login').focus();

        submit_button.click(function(e) {

            var login = $('#user-login').val();
            var password = $('#user-password').val();

            e.preventDefault();

            if (login == '' || password == '')
            {
                return false;
            }

            // Прячем уведомление об ошибке и выключаем кнопку
            alert.hide();
            submit_button.attr("disabled", "disabled");

            var form = $('#login-form');

            $.JSON("post", form.attr('action'), { "user-login": login, "user-password": password },
                function()
                {
                    // Всё в порядке, перенаправляем пользователя
                    location.href = "<?= $redirect_url ?>";
                },
                function(message)
                {
                    // Показываем сообщение об ошибке и включаем кнопку
                    alert.html(message).show();
                    submit_button.removeAttr("disabled");
                }
            );

        });
    });

</script>