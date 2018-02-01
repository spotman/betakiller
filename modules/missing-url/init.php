<?php
use BetaKiller\MissingUrl\Initializer;

/** @var Initializer $missingUrlInit */
$missingUrlInit = \BetaKiller\DI\Container::getInstance()->get(Initializer::class);
$missingUrlInit->init();
