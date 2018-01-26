<?php

Route::set('widget-controller', 'w/<widget>(/<action>)',
    array('widget' => '[A-Za-z_]+', 'action' => '[A-Za-z_]+'))
    ->defaults(array(
        'controller'    => 'Widget',
        'action'        => 'render',
    ));

// Make it last
Route::set('default-iface-controller', '(<uri>)', array('uri' => '.+'))
    ->defaults(array(
        'controller'    => 'IFace',
        'action'        => 'render',
    ));

$initializer = \BetaKiller\DI\Container::getInstance()->get(\BetaKiller\IFace\Initializer::class);
$initializer->init();
