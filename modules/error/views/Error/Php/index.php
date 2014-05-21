<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="navbar navbar-inner navbar-form form-inline">

    <a class="btn btn-default" href="/errors/php/action/toggle_show_resolved_errors">
        <?= $show_resolved_errors ? "Показать только открытые ошибки" : "Показать исправленные ошибки" ?>
    </a>

    <? if ( $show_resolved_errors ): ?>

        <label></label>
        <?= Form::select(
            "user-filter",
            array(NULL => "Все разработчики") + $developers_list,
            $user_id_filter,
            array("id" => "user-filter")
        ); ?>

    <? else: ?>
    <? endif; ?>

    <?

    $sort_keys = array(
        "time"      => "По времени",
        "module"    => "По модулю",
        "message"   => "По типу ошибки",
    );

    ?>

    <div class="btn-group pull-right">

        <? foreach ( $sort_keys as $sort_key => $sort_label ):

            $is_current = ($sort_key == $sort_by);

            $link = $is_current
                ? "/errors/php/action/toggle_sort_direction"
                : "/errors/php/action/set_sort_by/$sort_key";

            $title = $is_current
                ? "Изменить направление сортировки"
                : "Выбрать другой критерий сортировки";

            $icon = $sort_direction ? "icon-arrow-up" : "icon-arrow-down";
            ?>

        <a tabindex="-1" href="<?= $link ?>" title="<?= $title ?>" class="btn btn-default <?= $is_current ? "active" : "" ?>">
            <? if ( $is_current ): ?><i class="<?= $icon ?>"></i><? endif; ?> <?= $sort_label ?>
        </a>

        <? endforeach; ?>
    </div>

</div>

<script type="text/javascript">

    $(function()
    {
        var user_select = $("#user-filter");

        user_select.change(function()
        {
            var user_id = user_select.val();
            location.href = user_id ? "?user_id=" + user_id : "/errors/php/";
        });
    });

</script>

<? if ( ! count($errors) ): ?>
    <div class="alert alert-info">Ошибок не найдено</div>
<? return; endif; ?>

<style type="text/css">

    ul.unstyled {
        margin-bottom: 0;
    }

</style>

<table class="table table-hover">

    <thead>
        <tr>
            <th>Сообщение</th>
            <th>Действия</th>
        </tr>
    </thead>

    <tbody>
        <? foreach ( $errors as $error ): /** @var Model_Error_Message_Php $error */
            $link = "/errors/php/". $error->get_hash();
            $paths = $error->get_paths();
            $base_path = dirname(APPPATH);
            $message = Text::limit_chars($error->get_message(), 120, '...', FALSE);
            $time = $error->get_time();
            $module = $error->get_module();
            $is_resolved = $error->is_resolved();

            // Если ошибка сейчас исправлена, отмечаем её зелёным
            if ( $is_resolved )
            {
                $css_class = "success";
            }
            // Если ошибка сейчас не исправлена, но её раньше кто-то исправлял, отмечаем жёлтым
            elseif ( $error->get_resolved_by() )
            {
                $css_class = "warning";
            }
            else
            {
                $css_class = "";
            }

            ?>

            <tr class="item <?= $css_class ?>">
                <td>
                    <div class="pull-right">
                        <small class="label label-primary"><?= $module ?></small>
                        <small class="label label-primary"><?= date("H:i:s d.m.Y", $time) ?></small>
                    </div>
                    <a href="<?= $link ?>">
                        <strong><?= $message ?></strong>
                    </a><br />

                    <ul class="list-unstyled">
                        <? foreach ( $paths as $path ): ?>
                            <li><?= str_replace( $base_path, "", $path ) ?></li>
                        <? endforeach ?>
                    </ul>

                </td>
                <td>
                    <? if ( $is_resolved ): ?>
                        <a class="btn btn-danger" href="<?= $link ?>/delete"><i class="icon-remove"></i> Удалить</a>
                    <? else: ?>
                        <a class="btn btn-success" href="<?= $link ?>/resolved"><i class="icon-ok"></i> Уже исправлена</a>
                    <? endif; ?>
                </td>
            </tr>

        <? endforeach; ?>
    </tbody>

</table>