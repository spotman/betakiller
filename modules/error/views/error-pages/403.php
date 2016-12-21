<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<style>
    .center {
        position: absolute;
        width: 700px;
        height: auto;
        top: 50%;
        left: 50%;
        margin-top: -200px;
        margin-left: -350px;
        text-align: center;
        z-index: 10000;
    }

    .error-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        z-index: 9999;
    }
</style>

<div class="error-overlay"></div>
<div class="well center">
    <h2><?= $message ?: __("You have no enough privileges for viewing current page.") ?></h2>

    <? if ( ! @ Env::get('user') ): ?>
    <p><strong><?= __("Authorize, please") ?></strong></p><br />
    <a href="/login<?= Request::current() ? "?return=". Request::current()->detect_uri() : NULL; ?>" class="btn btn-large btn-info"><i class="icon-lock icon-white"></i> <?= __("Sign in") ?></a>
    <? else: ?>
<!--    <a href="/logout" class="btn btn-large btn-info"><i class="icon-lock icon-white"></i> --><?//= __("Выйти") ?><!--</a>-->
        <a  href="#" class="btn btn-large btn-info" onclick="window.history.back(-1);return false;">
            <i class="icon-lock icon-white"></i> <?= __("Back") ?>
        </a>
    <? endif; ?>
</div>
