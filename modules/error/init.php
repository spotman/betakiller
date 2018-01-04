<?php
use BetaKiller\Error\Initializer;

/** @var \BetaKiller\Error\Initializer $errorInit */
$errorInit = \BetaKiller\DI\Container::getInstance()->get(Initializer::class);
$errorInit->init();
