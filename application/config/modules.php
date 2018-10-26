<?php

return [
    // Raw dependencies (classes only, will be refactored to composer packages with PSR-4 autoload)
    'core'        => MODPATH.'core',                  // Core interfaces and classes
    'ddd'         => MODPATH.'ddd',                   // Interfaces and classes for DDD
    'helper'      => MODPATH.'helper',                // Common helpers without external dependencies
    'log'         => MODPATH.'log',                   // Logs subsystem
    'utils'       => MODPATH.'utils',                 // Useful classes
    'widget'      => MODPATH.'widget',                // Basic widgets support

    // Kohana dependencies without DB access (using Kohana config)
    'email'       => MODPATH.'email',                 // Mailing module
    'image'       => MODPATH.'image',                 // Image manipulation
    'i18n-plural' => MODPATH.'i18n-plural',           // International pluralization
    'message-bus' => MODPATH.'message-bus',           // Event and command buses
    'meta-tags'   => MODPATH.'meta-tags',             // HTML meta tags helper
    'minion'      => MODPATH.'minion',                // CLI Tasks
//    'security-headers'      => MODPATH.'security-headers',      // Send CSP and other security headers

    // Database layer with Kohana dependencies
    'database'    => MODPATH.'database',              // Database access
    'mysqli'      => MODPATH.'mysqli',                // Driver for MySQLi
    'sqlite'      => MODPATH.'sqlite',                // SQLite database driver (used by logs)
    'orm'         => MODPATH.'orm',                   // Object Relationship Mapping
    'paginate'    => MODPATH.'paginate',              // Paginate abstraction for ORM, ORM-REST and others

    // Kohana dependencies WITH DB ACCESS
    'backup'      => MODPATH.'backup',                // Complex backup (files + database)
    'migrations'  => MODPATH.'migrations',            // Migrations toolkit
    'unittest'    => MODPATH.'unittest',              // Module for unit testing via phpunit

    // Place it before other modules for correct initialization of per-site classes and configs
    'multi-site'  => MODPATH.'multi-site',            // Multiple apps on top of single engine

    // Initialize platform (AppEnv, DIC, etc)
    'platform'    => MODPATH.'platform',              // Platform implementation and initialization

    // These modules DEPENDS on PLATFORM and have NO legacy ROUTES
    'acl'          => MODPATH.'acl',                   // Role based access control
    'api'          => MODPATH.'api',                   // API subsystem
    'auth'         => MODPATH.'auth',                  // Auth IFaces and Widgets
    'assets'       => MODPATH.'assets',                // Asset management subsystem
    'cron'         => MODPATH.'cron',                  // Helper for running scheduled tasks
    'error'        => MODPATH.'error',                 // Error handling and logging
    'daemon'       => MODPATH.'daemon',                // Daemon run
    'iface'        => MODPATH.'iface',                 // Dynamic user interfaces
    'maintenance'  => MODPATH.'maintenance',           // Maintenance mode
    'missing-url'  => MODPATH.'missing-url',           // Catch and store missing IFace URLs
    'notification' => MODPATH.'notification',          // Notification subsystem
    'search'       => MODPATH.'search',                // Search and filtering capabilities
    'sitemap'      => MODPATH.'sitemap',               // Generating sitemap.xml
    'twig'         => MODPATH.'twig',                  // Twig template engine
    'wamp'         => MODPATH.'wamp',                  // WAMP protocol support (router + client)
    'webhooks'     => MODPATH.'webhooks',              // WebHooks control panel

    // Workaround for injecting admin/config/ifaces.xml in a proper way
    'admin'       => MODPATH.'admin',                 // Basic admin

    'outdatedbrowser' => MODPATH.'outdatedbrowser',   // Notification for older browsers
];
