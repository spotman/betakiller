<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'utils'                 => MODPATH.'utils',                 // Useful classes

    'error'                 => MODPATH.'error',                 // Error handling and logging

    // Place it first for correct initialization of per-site classes and configs
    'multi-site'            => MODPATH.'multi-site',            // Multiple apps on top of single engine

    'admin'                 => MODPATH.'admin',                 // Basic admin
    'api'                   => MODPATH.'api',                   // API subsystem
    'assets'                => MODPATH.'assets',                // Asset management subsystem
    'auth'                  => MODPATH.'auth',                  // Basic authentication
    'backup'                => MODPATH.'backup',                // Complex backup (files + database)
    'cache'                 => MODPATH.'cache',                 // Caching with multiple backends
    'database'              => MODPATH.'database',              // Database access
    'di'                    => MODPATH.'di',                    // Dependency Injection
//    'dbv'                   => MODPATH.'dbv',                   // Database Version Control
    'device'                => MODPATH.'device',                // Device detection
    'email'                 => MODPATH.'email',                 // Mailing module
//    'excel-import'          => MODPATH.'excel-import',          // Import Excel documents
    'geocode'               => MODPATH.'geocode',               // Geocoder-php wrapper
    'hashids'               => MODPATH.'hashids',               // youtu.be like hash id generator and parser
    'jsonrpc'               => MODPATH.'jsonrpc',               // JSON-RPC server
    'image'                 => MODPATH.'image',                 // Image manipulation
    'i18n-plural'           => MODPATH.'i18n-plural',           // International pluralization
//    'locator'               => MODPATH.'locator',               // URL mapping to interfaces
    'mangodb'               => MODPATH.'mangodb',               // ODM wrapper for mongodb
    'meta-tags'             => MODPATH.'meta-tags',             // HTML meta tags helper
    'migrations'            => MODPATH.'migrations',            // Migrations toolkit
    'minion'                => MODPATH.'minion',                // CLI Tasks
    'mysqli'                => MODPATH.'mysqli',                // Driver for MySQLi
    'orm'                   => MODPATH.'orm',                   // Object Relationship Mapping
    'paginate'              => MODPATH.'paginate',              // Paginate abstraction for ORM, ORM-REST and others
    'robots-txt'            => MODPATH.'robots-txt',            // Serving /robots.txt
    'static-files'          => MODPATH.'static-files',          // Static Files (JS/CSS/pictures)
    'twig'                  => MODPATH.'twig',                  // Twig template engine
    'ulogin'                => MODPATH.'ulogin',                // Universal login via ulogin.ru
//    'xmpp'                  => MODPATH.'xmpp',                  // Wrapper for XMPP (Jabber) protocol

    'sitemap'               => MODPATH.'sitemap',               // Generating sitemap.xml

    // Allow another modules to set routes
    'iface'                 => MODPATH.'iface',                 // Dynamic user interfaces

    'article'               =>  MODPATH.'article',              // Articles based on IFaces
);
