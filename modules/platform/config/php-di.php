<?php

use BetaKiller\Acl\AclResourceFactory;
use BetaKiller\Acl\AclResourcesCollector;
use BetaKiller\Acl\AclRolesCollector;
use BetaKiller\Acl\AclRulesCollector;
use BetaKiller\Acl\EntityPermissionResolver;
use BetaKiller\Acl\EntityPermissionResolverInterface;
use BetaKiller\Acl\UrlElementAccessResolver;
use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\Api\AccessResolver\CustomApiMethodAccessResolverDetector;
use BetaKiller\Api\ApiLanguageDetector;
use BetaKiller\Assets\StaticAssets;
use BetaKiller\Cache\DoctrineCacheProvider;
use BetaKiller\Config\AppConfig;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Config\WebConfig;
use BetaKiller\Config\WebConfigInterface;
use BetaKiller\DefenceParameterProviderFactory;
use BetaKiller\DummyIdentityConverter;
use BetaKiller\Exception;
use BetaKiller\Factory\AppRunnerFactory;
use BetaKiller\Factory\AppRunnerFactoryInterface;
use BetaKiller\Factory\EntityFactory;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\OrmFactory;
use BetaKiller\Factory\RepositoryFactory;
use BetaKiller\Factory\RepositoryFactoryInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\RequestLanguageHelperInterface;
use BetaKiller\IdentityConverterInterface;
use BetaKiller\MessageBus\BoundedEventTransportInterface;
use BetaKiller\MessageBus\CommandBus;
use BetaKiller\MessageBus\CommandBusInterface;
use BetaKiller\MessageBus\EsbBoundedEventTransport;
use BetaKiller\MessageBus\EsbOutboundEventTransport;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\MessageBus\EventSerializerInterface;
use BetaKiller\MessageBus\NativeEventSerializer;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use BetaKiller\Middleware\CspReportBodyParamsStrategy;
use BetaKiller\Notification\MessageActionUrlGeneratorInterface;
use BetaKiller\Notification\MessageRenderer;
use BetaKiller\Notification\MessageRendererInterface;
use BetaKiller\Notification\ScheduleTargetSpecInterface;
use BetaKiller\Notification\UserScheduleTargetSpec;
use BetaKiller\NotificationMessageActionUrlGenerator;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\TokenRepository;
use BetaKiller\Repository\TokenRepositoryInterface;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Repository\UserSessionRepository;
use BetaKiller\Repository\UserSessionRepositoryInterface;
use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\View\LayoutViewInterface;
use BetaKiller\View\TwigLayoutView;
use BetaKiller\View\TwigViewFactory;
use BetaKiller\View\ViewFactoryInterface;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Enqueue\Redis\RedisConnectionFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Http\Client\HttpClient;
use Interop\Queue\Context;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\Stratigility\MiddlewarePipeInterface;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouteCollectorInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionPersistenceInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;
use Spotman\Api\AccessResolver\ApiMethodAccessResolverDetectorInterface;
use Spotman\Api\ApiLanguageDetectorInterface;
use Spotman\Defence\Parameter\ParameterProviderFactoryInterface;
use function DI\autowire;
use function DI\factory;
use function DI\get;

$deprecatedFactory = factory(static function () {
    throw new LogicException('Deprecated');
});

return [

    'definitions' => [

        AppRunnerFactoryInterface::class => autowire(AppRunnerFactory::class),

        WebConfigInterface::class => autowire(WebConfig::class),

        RouteCollectorInterface::class  => autowire(RouteCollector::class),
        RouterInterface::class          => autowire(FastRouteRouter::class),
        EmitterInterface::class         => autowire(SapiStreamEmitter::class),
        MiddlewarePipeInterface::class  => autowire(MiddlewarePipe::class),
        RequestFactoryInterface::class  => autowire(RequestFactory::class),
        ResponseFactoryInterface::class => autowire(ResponseFactory::class),
        StreamFactoryInterface::class   => autowire(StreamFactory::class),
        UriFactoryInterface::class      => autowire(UriFactory::class),

        RequestHandlerRunner::class => factory(static function (
            MiddlewarePipeInterface $pipe,
            EmitterInterface        $emitter,
            LoggerInterface         $logger
        ) {
            return new RequestHandlerRunner(
                $pipe,
                $emitter,
                static function () {
                    return ServerRequestFactory::fromGlobals();
                },
                static function (Throwable $e) use ($logger) {
                    // Log exception to developers
                    $logger->alert(Exception::oneLiner($e), [
                        LoggerHelper::CONTEXT_KEY_EXCEPTION => $e,
                    ]);

                    // No exception info here for security reasons
                    return new TextResponse('System error', 500);
                }
            );
        }),

        ImplicitHeadMiddleware::class => factory(static function (
            RouterInterface        $router,
            StreamFactoryInterface $factory
        ) {
            return new ImplicitHeadMiddleware($router, static function () use ($factory) {
                return $factory->createStream();
            });
        }),

        ImplicitOptionsMiddleware::class => factory(static function (ResponseFactoryInterface $factory) {
            return new ImplicitOptionsMiddleware(static function () use ($factory) {
                return $factory->createResponse();
            });
        }),

        MethodNotAllowedMiddleware::class => factory(static function (ResponseFactoryInterface $factory) {
            return new MethodNotAllowedMiddleware(static function () use ($factory) {
                return $factory->createResponse();
            });
        }),

//        RequestIdMiddleware::class => DI\factory(function () {
//            $generator         = new \PhpMiddleware\RequestId\Generator\PhpUniqidGenerator();
//            $requestIdProvider = new \PhpMiddleware\RequestId\RequestIdProviderFactory($generator);
//
//            return new RequestIdMiddleware($requestIdProvider);
//        }),

        BodyParamsMiddleware::class => factory(static function () {
            $params = new BodyParamsMiddleware();

            $params->addStrategy(new CspReportBodyParamsStrategy());

            return $params;
        }),

        SessionStorageInterface::class     => autowire(DatabaseSessionStorage::class),
        SessionPersistenceInterface::class => get(SessionStorageInterface::class),

        // Deprecated DI objects
        // UrlHelper is used via Container::make() method and can not be deprecated
        UrlElementStack::class             => factory(static function () {
            throw new LogicException(UrlElementStack::class.' DI injection deprecated, use ServerRequestHelper::getUrlElementStack()');
        }),

        UrlContainerInterface::class => factory(static function () {
            throw new LogicException(UrlContainerInterface::class.' DI injection deprecated, use ServerRequestHelper::getUrlContainer() instead');
        }),

        I18nHelper::class => factory(static function () {
            throw new Exception(I18nHelper::class.' DI injection deprecated, use ServerRequestHelper::getI18n() instead');
        }),

        RequestLanguageHelperInterface::class => factory(static function () {
            throw new Exception(RequestLanguageHelperInterface::class.' DI injection deprecated, use ServerRequestHelper::getI18n() instead');
        }),

        EntityFactoryInterface::class => autowire(EntityFactory::class),

        \GuzzleHttp\ClientInterface::class => factory(static function (LoggerInterface $logger) {
            $stack = HandlerStack::create();

            $stack->push(
                Middleware::log(
                    $logger,
                    new \GuzzleHttp\MessageFormatter('{req_headers} => {res_headers}'),
                    LogLevel::DEBUG
                )
            );

            return new Client([
                'handler'     => $stack,
                'http_errors' => false,
            ]);
        }),

        ClientInterface::class => autowire(\Http\Adapter\Guzzle7\Client::class),

        Context::class => DI\factory(static function (AppEnvInterface $appEnv) {
            $prefix = implode('.', [
                $appEnv->getAppCodename(),
                $appEnv->getRevisionKey(),
                $appEnv->getModeName(),
            ]);

            $factory = new RedisConnectionFactory([
                'scheme_extensions' => ['phpredis',],

                'host'  => getenv('REDIS_HOST'),
                'port'  => getenv('REDIS_PORT'),
                'lazy'  => true,
                'async' => true,

                'predis_options' => [
                    'prefix' => $prefix,
                ],

                'redelivery_delay' => 300,
            ]);

            return $factory->createContext();
        }),

        NamespaceBasedFactoryBuilderInterface::class => autowire(NamespaceBasedFactoryBuilder::class),

        RepositoryFactoryInterface::class => autowire(RepositoryFactory::class),

        UserRepositoryInterface::class        => autowire(UserRepository::class),
        UserSessionRepositoryInterface::class => autowire(UserSessionRepository::class),
        RoleRepositoryInterface::class        => autowire(RoleRepository::class),
        TokenRepositoryInterface::class       => autowire(TokenRepository::class),

        IdentityConverterInterface::class => autowire(DummyIdentityConverter::class),

        ParameterProviderFactoryInterface::class => autowire(DefenceParameterProviderFactory::class),

        ApiLanguageDetectorInterface::class => autowire(ApiLanguageDetector::class),

        MessageActionUrlGeneratorInterface::class => autowire(NotificationMessageActionUrlGenerator::class),

        UuidFactoryInterface::class              => autowire(UuidFactory::class),

        // Basic implementation
        UrlElementAccessResolverInterface::class => autowire(UrlElementAccessResolver::class),
        EntityPermissionResolverInterface::class => autowire(EntityPermissionResolver::class),

        EventSerializerInterface::class => autowire(NativeEventSerializer::class),

        ScheduleTargetSpecInterface::class => autowire(UserScheduleTargetSpec::class),

        // Single loop instance for simplicity
        LoopInterface::class               => factory(static function () {
            return Loop::get();
        }),

        OrmFactoryInterface::class => autowire(OrmFactory::class),

        // PSR-16 adapter for system-wide Doctrine Cache
        CacheInterface::class      => DI\factory(function (Cache $doctrineCache) {
            return new SimpleCacheAdapter($doctrineCache);
        }),

        // Bind Doctrine cache interface to abstract cache provider
        Cache::class               => DI\get(CacheProvider::class),

        // Common cache instance for all
        CacheProvider::class       => DI\get(DoctrineCacheProvider::class),

        AppConfigInterface::class                       => DI\autowire(AppConfig::class),

        // Acl roles, resources, permissions and resource factory
        AclRolesCollectorInterface::class               => DI\autowire(AclRolesCollector::class),
        AclResourcesCollectorInterface::class           => DI\autowire(AclResourcesCollector::class),
        AclRulesCollectorInterface::class               => DI\autowire(AclRulesCollector::class),
        AclResourceFactoryInterface::class              => DI\autowire(AclResourceFactory::class),

        // Use Twig as default view
        ViewFactoryInterface::class                     => DI\autowire(TwigViewFactory::class),
        // Use Twig in layouts
        LayoutViewInterface::class                      => DI\autowire(TwigLayoutView::class),

        // Custom access resolver detector
        ApiMethodAccessResolverDetectorInterface::class => DI\autowire(CustomApiMethodAccessResolverDetector::class),

        // Use default renderer for notification messages
        MessageRendererInterface::class                 => DI\autowire(MessageRenderer::class),

        Meta::class => \DI\factory(function () {
            throw new LogicException('DI injection of class Meta is deprecated');
        }),

        StaticAssets::class => \DI\factory(function () {
            throw new LogicException('DI injection of class StaticAssets is deprecated');
        }),

        BoundedEventTransportInterface::class  => DI\autowire(EsbBoundedEventTransport::class),
        OutboundEventTransportInterface::class => DI\autowire(EsbOutboundEventTransport::class),

        EventBusInterface::class   => DI\factory(static function (
            ContainerInterface      $container,
            EventBus                $bus,
            ConfigProviderInterface $config
        ) {
            // For each event
            foreach ((array)$config->load(['events']) as $eventName => $handlers) {
                // Fetch all handlers
                foreach ($handlers as $handlerClassName) {
                    // Bind lazy-load wrapper
                    $bus->on($eventName, static function ($event) use ($container, $handlerClassName) {
                        $handler = $container->get($handlerClassName);

                        $handler($event);
                    });
                }
            }

            return $bus;
        }),

        // Handlers will be added in workers
        CommandBusInterface::class => DI\autowire(CommandBus::class),
    ],

];
