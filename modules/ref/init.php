<?php
use BetaKiller\Ref\Initializer;

/** @var Initializer $refInit */
$refInit = \BetaKiller\DI\Container::getInstance()->get(Initializer::class);
$refInit->init();
