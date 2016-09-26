<?php defined('SYSPATH') OR die('No direct script access.');

$enable_profiler_text = "Включить профайлер";
$disable_profiler_text = "Отключить профайлер";
$profiler_button_text = $is_profiler_enabled ? $disable_profiler_text : $enable_profiler_text;

?>

<div class="row-fluid">
    <div id="error-widget" class="span12">
        <div class="btn-toolbar">

            <!-- кол-во ошибок -->
            <?php if ( $error_count ): ?>
                <a class="btn btn-link" href="http://k.planet.web/errors/php" target="_blank">
                    Есть ошибки в PHP <span class="label label-important"><?= $error_count ?></span>
                </a>
            <?php else: ?>
                <button class="btn btn-link" disabled="disabled">
                    <?= ( $error_count === NULL ) ? "Консоль ошибок недоступна" : "Ошибок в PHP нет" ?>
                </button>
            <?php endif; ?>

            <!-- профайлер -->
            <button class="btn pull-right toggle-profiler <?= $is_profiler_enabled ? "profiler-enabled btn-primary" : "" ?>"
                    data-text-enable="<?= $enable_profiler_text?>" data-text-disable="<?= $disable_profiler_text?>">
                <?= $profiler_button_text ?>
            </button>

            <button class="btn clear-static-cache pull-right">Сбросить js/css кеш</button>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(function()
    {
        var widget = $("#error-widget");
        var profiler_button = widget.find(".toggle-profiler");
        var clear_cache_button = widget.find(".clear-static-cache");
        var hide_pane_cookie_name = "developer-pane-is-hidden";

        profiler_button.click(function()
        {
            profiler_button.attr("disabled", "disabled");

            $.post("/errors/widget/toggle_profiler").done(function()
            {
                var text = profiler_button.hasClass("profiler-enabled")
                    ? profiler_button.data("text-enable")
                    : profiler_button.data("text-disable");

                profiler_button
                    .toggleClass("profiler-enabled btn-primary")
                    .removeAttr("disabled")
                    .text(text);
            });
        });

        clear_cache_button.click(function()
        {
            clear_cache_button.attr("disabled", "disabled");

            $.post("/!/clear").done(function()
            {
                clear_cache_button.removeAttr("disabled");
                $.notify.success("Кеш статических файлов сброшен");
            });

        });

        var is_hidden = ($.cookie(hide_pane_cookie_name) == 'true');

        if ( is_hidden )
        {
            widget.addClass("hide");
        }

        $("#toggle-developer-pane").click(function(e)
        {
            e.preventDefault();

            widget.toggleClass("hide");
            $.cookie(hide_pane_cookie_name, ( !is_hidden ? "true" : "false" ));
        });

    });

</script>
