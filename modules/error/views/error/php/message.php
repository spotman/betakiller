<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="navbar navbar-inner">
    <a class="btn btn-info" href="/errors/php"><i class="icon-chevron-left"></i> Назад к списку</a>

    <? if ( $is_resolved): ?>
        <a class="btn btn-danger pull-right" href="/errors/php/<?= $hash ?>/delete"><i class="icon-remove"></i> Удалить ошибку</a>
    <? else: ?>
        <a class="btn btn-success pull-right" href="/errors/php/<?= $hash ?>/resolved"><i class="icon-ok"></i> Ошибка исправлена</a>
    <? endif; ?>
</div>

<div>

    Ошибка встречается <span class="label label-important"><?= $counter ?></span> раз (последний раз <span class="label label-info"><?= date("d.m.Y в H:i:s", $time) ?></span>) по следующим адресам:<br />

    <ul>
        <? foreach ( $urls as $url ): ?>
            <li><?= $url ?></li>
        <? endforeach ?>
    </ul><br />

    в следующих файлах:<br />

    <ul>
        <? foreach ( $paths as $path ): ?>
            <li><?= $path ?></li>
        <? endforeach ?>
    </ul><br />

    История изменений:
    <ul>
        <?
        foreach ( $history as $item ): /** @var $item stdClass */ ?>
            <li><?= __(":what :who :when", array(":who" => $item->who, ":when" => $item->when, ":what" => $item->what))
                ?></li>
        <? endforeach ?>
    </ul>

</div>

<div class="">
    <legend>Оригинальный стектрейс</legend>
    <?= $trace ?>
</div>