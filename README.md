[![Build Status](https://travis-ci.org/spotman/betakiller.svg?branch=master)](https://travis-ci.org/spotman/betakiller)

# Betakiller platform

Based on legacy [Kohana](http://kohanaframework.org/) framework (migration to [PSR-7](https://github.com/php-fig/http-message) / [PSR-15](https://github.com/http-interop/http-middleware) planned).

Under the hood:

- [Twig](https://twig.symfony.com/) as a template engine ([core extension](application/classes/BetaKiller/Twig/Extension.php))
- [PHP-DI](http://php-di.org/) as DI container (with autowiring enabled, [core config](application/config/twig.php))
- DDD used for core development (Entities, Repositories, Factories, etc)
- Unique [IFace module](modules/iface) for easier organizing server-side part of UI
- [Custom ACL subsystem](modules/acl) with dedicated resource classes (Zend ACL on steroids)
- Simple [Message bus](modules/message-bus) for inter-modular communication
- [JSON-RPC API](modules/api) with "<Collection>.<Method>" hierarchy and automatic ACL binding
- [Migrations](modules/migrations) with CLI helper and ability to create migrations for module, core or application
- [Custom error management](modules/error) with logging to local SQLite3 DB
- [Notifications subsystem](modules/notification) with multiple providers for simpler communication with users
- Multiple sites on a single core via [MultiSite module](modules/multi-site)
- [Kohana ORM](modules/orm) as current DBAL (migration to [Propel3](https://github.com/propelorm/Propel3) planned)
- [Deployer](https://deployer.org/) as default deploying tool
- [Monolog](https://github.com/Seldaek/monolog) as default logger (with ChromePHP for online debugging)
- [Minion](modules/minion) as CLI task subsystem
- [HTML meta-tags](modules/meta-tags), [robots.txt](modules/robots-txt/README.md) and [sitemap.xml](modules/sitemap) processors
- [Multiple useful helpers](modules/utils/README.md) for Kohana-based projects
- Multiple helpers like AppConfig and AppEnv
- A lot of improvements are on the go

Released under a [BSD license](LICENSE.md), Betakiller can be used legally for any open source, commercial, or personal project.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cae4439a-8a2e-4e0c-9169-f9d1c7e25366/big.png)](https://insight.sensiolabs.com/projects/cae4439a-8a2e-4e0c-9169-f9d1c7e25366)
