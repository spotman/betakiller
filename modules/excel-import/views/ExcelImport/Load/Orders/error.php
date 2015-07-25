<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<!-- <h1>Потребление памяти: <?= $memory_usage ?></h1> -->

<p>Обработано заявок: <strong><?= $order_counter ?></strong></p><br />


<div <?php if( ! $failed_numbers ) echo 'style="display:none"'; ?>>
    <p>В следующих строках произошли ошибки обработки.<br />Исправьте эти строки, после чего перезапустите загрузку.</p><br />

        <? foreach($failed_numbers as $number => $errors_array): ?>
        <ul><?= 'Строка '.$number.':' ?>
            <? foreach ( $errors_array as $error ) :?>
                <li><?= $error ?></li>
            <? endforeach; ?>
        </ul>
        <? endforeach; ?>
</div>

