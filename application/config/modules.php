<?php defined('SYSPATH') OR die('No direct script access.');

return array(
    'api'                   => MODPATH.'api',                   // API subsystem
    'auth'                  => MODPATH.'auth',                  // Basic authentication
    'cache'                 => MODPATH.'cache',                 // Caching with multiple backends
//    'codebench'             => MODPATH.'codebench',             // Benchmarking tool
    'database'              => MODPATH.'database',              // Database access
    'email'                 => MODPATH.'email',                 // Mailing module
    'error'                 => MODPATH.'error',                 // Модуль отлова и логирования ошибок
    'iface'                 => MODPATH.'iface',                 // Dynamic user interfaces
    'image'                 => MODPATH.'image',                 // Image manipulation
    'kohana-static-files'   => MODPATH.'kohana-static-files',   // Static Files (JS/CSS/pictures)
    'locator'               => MODPATH.'locator',               // URL mapping to interfaces
    'mangodb'               => MODPATH.'mangodb',               // ODM обёртка для mongodb
    'multi-site'            => MODPATH.'multi-site',            // Multiple apps on top of single engine
    'minion'                => MODPATH.'minion',                // CLI Tasks
    'orm'                   => MODPATH.'orm',                   // Object Relationship Mapping
    // 'unittest'   => MODPATH.'unittest',   // Unit testing
    // 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
    'xmpp'                  => MODPATH.'xmpp',                  // Обёртка для взаимодействия по протоколу XMPP (Jabber)
    'ulogin'                => MODPATH.'ulogin',                // Universal login via ulogin.ru
);