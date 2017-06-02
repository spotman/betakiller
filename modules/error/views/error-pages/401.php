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
    <h2><?php echo $message ?: __('You need to login before going further.') ?></h2>

    <?php if (!Env::get('user')): ?>
        <a href="/login<?php echo (Request::current() ? '?redirect_url='.Request::current()->detect_uri() : null); ?>"
           class="btn btn-lg btn-info"><i class="icon-lock icon-white"></i> <?php echo __('Sign in') ?></a>
    <?php else: ?>
        <a href="#" class="btn btn-large btn-info" onclick="window.history.back();return false;">
            <i class="icon-lock icon-white"></i> <?php echo __('Back') ?>
        </a>
    <?php endif; ?>
</div>
