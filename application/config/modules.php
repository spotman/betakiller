<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'error'                 => MODPATH.'error',                 // Модуль отлова и логирования ошибок

    // Place it first for correct initialization of per-site classes and configs
    'multi-site'            => MODPATH.'multi-site',            // Multiple apps on top of single engine

    'api'                   => MODPATH.'api',                   // API subsystem
    'auth'                  => MODPATH.'auth',                  // Basic authentication
    'cache'                 => MODPATH.'cache',                 // Caching with multiple backends
    'database'              => MODPATH.'database',              // Database access
    'email'                 => MODPATH.'email',                 // Mailing module
    'iface'                 => MODPATH.'iface',                 // Dynamic user interfaces
    'image'                 => MODPATH.'image',                 // Image manipulation
    'kohana-static-files'   => MODPATH.'kohana-static-files',   // Static Files (JS/CSS/pictures)
    'locator'               => MODPATH.'locator',               // URL mapping to interfaces
    'mangodb'               => MODPATH.'mangodb',               // ODM обёртка для mongodb
    'minion'                => MODPATH.'minion',                // CLI Tasks
    'orm'                   => MODPATH.'orm',                   // Object Relationship Mapping
    'twig'                  => MODPATH.'twig',                  // Twig template engine
    'ulogin'                => MODPATH.'ulogin',                // Universal login via ulogin.ru
//    'xmpp'                  => MODPATH.'xmpp',                  // Обёртка для взаимодействия по протоколу XMPP (Jabber)
);