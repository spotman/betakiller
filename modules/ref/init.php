<?php

$refInit = \BetaKiller\DI\Container::getInstance()->get(\BetaKiller\Ref\Initializer::class);
$refInit->init();
