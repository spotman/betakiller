<?php

return array(

    // Place it first for correct initialization of per-site classes and configs
    'multi-site'            => MODPATH.'multi-site',            // Multiple apps on top of single engine

    'utils'                 => MODPATH.'utils',                 // Useful classes
    'log'                   => MODPATH.'log',                   // Logs subsystem
    'sqlite'                => MODPATH.'sqlite',                // SQLite database driver (used by logs)
    'error'                 => MODPATH.'error',                 // Error handling and logging
    'message-bus'           => MODPATH.'message-bus',           // Event and command buses

    // Process legacy routing first
    'static-files'          => MODPATH.'static-files',          // Static Files (JS/CSS/pictures)

    'acl'                   => MODPATH.'acl',                   // Role based access control
    'admin'                 => MODPATH.'admin',                 // Basic admin
    'api'                   => MODPATH.'api',                   // API subsystem
    'assets'                => MODPATH.'assets',                // Asset management subsystem
    'auth'                  => MODPATH.'auth',                  // Basic authentication
    'backup'                => MODPATH.'backup',                // Complex backup (files + database)
    'cron'                  => MODPATH.'cron',                  // helper for running scheduled tasks
    'database'              => MODPATH.'database',              // Database access
    'device'                => MODPATH.'device',                // Device detection
    'email'                 => MODPATH.'email',                 // Mailing module
    'jsonrpc'               => MODPATH.'jsonrpc',               // JSON-RPC server
    'image'                 => MODPATH.'image',                 // Image manipulation
    'i18n-plural'           => MODPATH.'i18n-plural',           // International pluralization
    'meta-tags'             => MODPATH.'meta-tags',             // HTML meta tags helper
    'migrations'            => MODPATH.'migrations',            // Migrations toolkit
    'minion'                => MODPATH.'minion',                // CLI Tasks
    'mysqli'                => MODPATH.'mysqli',                // Driver for MySQLi
    'notification'          => MODPATH.'notification',          // Notification subsystem
    'orm'                   => MODPATH.'orm',                   // Object Relationship Mapping
    'paginate'              => MODPATH.'paginate',              // Paginate abstraction for ORM, ORM-REST and others
    'robots-txt'            => MODPATH.'robots-txt',            // Serving /robots.txt
    'twig'                  => MODPATH.'twig',                  // Twig template engine
    'ulogin'                => MODPATH.'ulogin',                // Universal login via ulogin.ru
    'unittest'              => MODPATH.'unittest',              // Module for unit testing via phpunit

    'sitemap'               => MODPATH.'sitemap',               // Generating sitemap.xml

    // Allow another modules to set routes
    'iface'                 => MODPATH.'iface',                 // Dynamic user interfaces
);
