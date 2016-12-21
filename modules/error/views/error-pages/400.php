<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<style>
    .well.center {
        position: absolute;
        width: 700px;
        height: auto;
        top: 50%;
        left: 50%;
        margin-top: -100px;
        margin-left: -350px;
        text-align: center;
    }

    .well.center span {
        font-size: 32px !important;
        padding: 12px !important;
    }
</style>

<div class="well center">
    <h2><span class="label label-important"><?= __("Wrong HTTP request, check your browser.") ?></span></h2>
    <h4><?= $message ?></h4>
</div>
