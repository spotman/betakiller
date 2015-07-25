<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<!-- <h1>Потребление памяти: <?= $memory_usage ?></h1> -->

<p>Обработано заявок: <strong><?= $order_counter ?></strong></p><br />


<div <?php if ( ! $failed_numbers ) echo 'style="display:none"'; ?>>
    <p>Заявки со следующими номерами обработаны с ошибками.<br />Пожалуйста, обработайте их вручную.</p><br />

    <? foreach ( $failed_numbers as $number => $msg ): ?>
    <p><?= ( $number ? '<strong>'. $number .'</strong> - ' : '') . $msg ?></p><br />
    <? endforeach; ?>
</div>

