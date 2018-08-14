<?php

return [

    // Raw dependencies (classes only, will be refactored to composer packages with PSR-4 autoload)
    'core'                  => MODPATH.'core',                  // Core interfaces and classes
    'ddd'                   => MODPATH.'ddd',                   // Interfaces and classes for DDD
    'helper'                => MODPATH.'helper',                // Common helpers without external dependencies
    'utils'                 => MODPATH.'utils',                 // Useful classes

    // Place it before other modules for correct initialization of per-site classes and configs
    'multi-site'            => MODPATH.'multi-site',            // Multiple apps on top of single engine

    // Initialize platform (AppEnv, DIC, etc)
    'platform'              => MODPATH.'platform',              // Platform implementation and initialization

    'log'                   => MODPATH.'log',                   // Logs subsystem
    'sqlite'                => MODPATH.'sqlite',                // SQLite database driver (used by logs)
    'error'                 => MODPATH.'error',                 // Error handling and logging
    'message-bus'           => MODPATH.'message-bus',           // Event and command buses

    // Process legacy routing first
    'static-files'          => MODPATH.'static-files',          // Static Files (JS/CSS/pictures)

    'acl'                   => MODPATH.'acl',                   // Role based access control
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
    'missing-url'           => MODPATH.'missing-url',           // Catch and store missing IFace URLs
    'mysqli'                => MODPATH.'mysqli',                // Driver for MySQLi
    'notification'          => MODPATH.'notification',          // Notification subsystem
    'orm'                   => MODPATH.'orm',                   // Object Relationship Mapping
    'paginate'              => MODPATH.'paginate',              // Paginate abstraction for ORM, ORM-REST and others
    'robots-txt'            => MODPATH.'robots-txt',            // Serving /robots.txt
    'search'                => MODPATH.'search',                // Search and filtering capabilities
//    'security-headers'      => MODPATH.'security-headers',      // Send CSP and other security headers
    'sitemap'               => MODPATH.'sitemap',               // Generating sitemap.xml
    'twig'                  => MODPATH.'twig',                  // Twig template engine
    'unittest'              => MODPATH.'unittest',              // Module for unit testing via phpunit
    'widget'                => MODPATH.'widget',                // Basic widgets support

    // Workaround for injecting admin/config/ifaces.xml in a proper way
    'admin'                 => MODPATH.'admin',                 // Basic admin

    // Allow another modules to set routes
    'iface'                 => MODPATH.'iface',                 // Dynamic user interfaces
];
