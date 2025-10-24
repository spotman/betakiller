<?php

return [
    // Raw dependencies (classes only, will be refactored to composer packages with PSR-4 autoload)
    'core'         => MODPATH.'core',                  // Core interfaces and classes
    'ddd'          => MODPATH.'ddd',                   // Interfaces and classes for DDD
    'helper'       => MODPATH.'helper',                // Common helpers without external dependencies
    'defence'      => MODPATH.'defence',               // Process insecure input (sanitize and validate)
    'log'          => MODPATH.'log',                   // Logs subsystem
    'monitoring'   => MODPATH.'monitoring',            // Monitoring subsystem
    'utils'        => MODPATH.'utils',                 // Useful classes
    'widget'       => MODPATH.'widget',                // Basic widgets support

    // Kohana dependencies without DB access (using Kohana config)
    'email'        => MODPATH.'email',                 // Mailing module
    'image'        => MODPATH.'image',                 // Image manipulation
//    'i18n-plural'  => MODPATH.'i18n-plural',           // International pluralization
    'message-bus'  => MODPATH.'message-bus',           // Event and command buses
    'meta-tags'    => MODPATH.'meta-tags',             // HTML meta tags helper
    'console'      => MODPATH.'console',               // CLI Tasks

    // Database layer with Kohana dependencies
    'database'     => MODPATH.'database',              // Database access
    'mysqli'       => MODPATH.'mysqli',                // Driver for MySQLi
    'sqlite'       => MODPATH.'sqlite',                // SQLite database driver (used by logs)
    'orm'          => MODPATH.'orm',                   // Object Relationship Mapping
    'paginate'     => MODPATH.'paginate',              // Paginate abstraction for ORM, ORM-REST and others

    // Kohana dependencies WITH DB ACCESS
//    'backup'       => MODPATH.'backup',                // Complex backup (files + database)
    'migrations'   => MODPATH.'migrations',            // Migrations toolkit
    'unittest'     => MODPATH.'unittest',              // Module for unit testing via phpunit

    // Place it before other modules for correct initialization of per-site classes and configs
//    'multi-site'   => MODPATH.'multi-site',            // Multiple apps on top of single engine

    // Platform defaults (DI config, presets, etc)
    'platform'     => MODPATH.'platform',              // Platform implementation

    // These modules DEPEND on PLATFORM and have NO legacy ROUTES
    'acl'          => MODPATH.'acl',                   // Role based access control
    'api'          => MODPATH.'api',                   // API subsystem
//    'api-test'     => MODPATH.'api-test',              // CLI and UI helpers for testing API subsystem
    'auth'         => MODPATH.'auth',                  // Auth IFaces and Widgets
    'assets'       => MODPATH.'assets',                // Asset management subsystem
    'cron'         => MODPATH.'cron',                  // Helper for running scheduled tasks
    'error'        => MODPATH.'error',                 // Error handling and logging
    'daemon'       => MODPATH.'daemon',                // Daemon run
    'geo'          => MODPATH.'geo',                   // Various geo utilities (geocoder, etc)
    'i18n'         => MODPATH.'i18n',                  // Localization and pluralization
    'iface'        => MODPATH.'iface',                 // Dynamic user interfaces
    'maintenance'  => MODPATH.'maintenance',           // Maintenance mode
    'menu'         => MODPATH.'menu',                  // Navigation menu processing
//    'hit-stat'     => MODPATH.'hit-stat',              // Catch and store missing URLs + save refs and make simple stat
    'notification' => MODPATH.'notification',          // Notification subsystem
    'robots-txt'   => MODPATH.'robots-txt',            // Serve different robots.txt for different environments
    'search'       => MODPATH.'search',                // Search and filtering capabilities
    'security'     => MODPATH.'security',              // Various security helpers
    //'csp'          => MODPATH.'csp',                   // Send CSP and other security headers
    'sitemap'      => MODPATH.'sitemap',               // Generating sitemap.xml
    'twig'         => MODPATH.'twig',                  // Twig template engine
//    'wamp'         => MODPATH.'wamp',                  // WAMP protocol support (router + client)
    'webhooks'     => MODPATH.'webhooks',              // WebHooks control panel
    'workflow'     => MODPATH.'workflow',              // Status workflow processing

    // Workaround for injecting admin/config/ifaces.xml in a proper way
    'admin'        => MODPATH.'admin',                 // Basic admin

    // Various helper modules
//    'outdated-browser'     => MODPATH.'outdated-browser',
];
