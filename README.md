# Betakiller platform

[![Build Status](https://travis-ci.org/spotman/betakiller.svg?branch=master)](https://travis-ci.org/spotman/betakiller)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/spotman/betakiller.svg)](http://isitmaintained.com/project/spotman/betakiller "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/spotman/betakiller.svg)](http://isitmaintained.com/project/spotman/betakiller "Percentage of issues still open")

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cae4439a-8a2e-4e0c-9169-f9d1c7e25366/big.png)](https://insight.sensiolabs.com/projects/cae4439a-8a2e-4e0c-9169-f9d1c7e25366)

Based on the legacy [Kohana](http://kohanaframework.org/) framework (migration to [PSR-7](https://github.com/php-fig/http-message) / [PSR-15](https://github.com/http-interop/http-middleware) planned).

Under the hood:

- [Twig](https://twig.symfony.com/) as a template engine ([core extension](application/classes/BetaKiller/Twig/Extension.php))
- [PHP-DI](http://php-di.org/) as DI container (with autowiring enabled, [core config](application/config/twig.php))
- [DDD](modules/ddd) used for core development (Entities, Repositories, Factories, etc)
- Unique [IFace module](modules/iface) for easier organizing server-side part of UI
- [Custom ACL subsystem](https://github.com/spotman/rbac) with dedicated resource classes (Zend ACL on steroids)
- Simple [Message bus](modules/message-bus) for inter-modular communication
- [JSON-RPC API](https://github.com/spotman/kohana-simple-api) with "Collection.Method" hierarchy and automatic ACL binding
- [Migrations](https://github.com/spotman/kohana-minion-migrations) with CLI helper and ability to create migrations for module, core or application
- [Custom error management](modules/error) with logging to local SQLite3 DB
- [Notifications subsystem](modules/notification) with multiple providers for simpler communication with users
- Multiple sites on a single core via [MultiSite module](https://github.com/spotman/kohana-multi-site)
- [Kohana ORM](modules/orm) as current DBAL/ORM (migration to [Propel3](https://github.com/propelorm/Propel3) planned)
- [Deployer](https://deployer.org/) as default deploying tool
- [Monolog](https://github.com/Seldaek/monolog) as default logger (with ChromePHP for online debugging)
- [Minion](modules/minion) as CLI task subsystem (symfony/console migration planned)
- [HTML meta-tags](https://github.com/spotman/kohana-meta-tags), [robots.txt](https://github.com/spotman/kohana-robots.txt) and [sitemap.xml](modules/sitemap) processors
- [Multiple useful helpers](https://github.com/spotman/kohana-utils) for Kohana-based projects
- Multiple helpers like AppConfig and AppEnv
- A lot of improvements are on the go

Released under a [BSD license](LICENSE.md), Betakiller can be used legally for any open source, commercial, or personal project.
