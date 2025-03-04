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
use BetaKiller\Cache\SymfonyCacheProvider;
use BetaKiller\Config\AppConfig;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Config\EventBusConfig;
use BetaKiller\Config\WebConfig;
use BetaKiller\Config\WebConfigInterface;
use BetaKiller\Console\ConsoleOptionBuilder;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskLocator;
use BetaKiller\Console\ConsoleTaskLocatorInterface;
use BetaKiller\DefenceParameterProviderFactory;
use BetaKiller\Dev\DebugBarAccessControl;
use BetaKiller\Dev\DebugBarAccessControlInterface;
use BetaKiller\Dev\DebugBarFactory;
use BetaKiller\Dev\DebugBarFactoryInterface;
use BetaKiller\DummyIdentityConverter;
use BetaKiller\Env\AppEnvInterface;
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
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\RequestLanguageHelperInterface;
use BetaKiller\IdentityConverterInterface;
use BetaKiller\MessageBus\BoundedEventTransportInterface;
use BetaKiller\MessageBus\CommandBus;
use BetaKiller\MessageBus\CommandBusInterface;
use BetaKiller\MessageBus\EsbBoundedEventTransport;
use BetaKiller\MessageBus\EsbOutboundEventTransport;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\EventBusConfigInterface;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\MessageBus\EventSerializerInterface;
use BetaKiller\MessageBus\NativeEventSerializer;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use BetaKiller\MezzioWebAppRunner;
use BetaKiller\Middleware\CspReportBodyParamsStrategy;
use BetaKiller\Notification\MessageActionUrlGeneratorInterface;
use BetaKiller\Notification\MessageRenderer;
use BetaKiller\Notification\MessageRendererInterface;
use BetaKiller\Notification\MessageTargetResolverInterface;
use BetaKiller\Notification\ScheduleTargetSpecInterface;
use BetaKiller\Notification\UserMessageTargetResolver;
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
use BetaKiller\Repository\UserStateRepository;
use BetaKiller\Repository\UserStateRepositoryInterface;
use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\View\LayoutViewInterface;
use BetaKiller\View\TwigLayoutView;
use BetaKiller\View\TwigViewFactory;
use BetaKiller\View\ViewFactoryInterface;
use BetaKiller\Web\ServerRequestErrorResponseGenerator;
use BetaKiller\WebAppRunnerInterface;
use Enqueue\Redis\RedisConnectionFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Interop\Queue\Context;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use Mezzio\ConfigProvider as MezzioConfigProvider;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouteCollectorInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionPersistenceInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
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
use Spotman\Acl\ResourceFactory\AclResourceFactoryInterface;
use Spotman\Acl\ResourcesCollector\AclResourcesCollectorInterface;
use Spotman\Acl\RolesCollector\AclRolesCollectorInterface;
use Spotman\Acl\RulesCollector\AclRulesCollectorInterface;
use Spotman\Api\AccessResolver\ApiMethodAccessResolverDetectorInterface;
use Spotman\Api\ApiLanguageDetectorInterface;
use Spotman\Defence\Parameter\ParameterProviderFactoryInterface;
use Symfony\Component\Cache\Psr16Cache;

use function DI\autowire;
use function DI\factory;
use function DI\get;

$deprecatedFactory = factory(static function () {
    throw new LogicException('Deprecated');
});

$mezzioConfig = (new MezzioConfigProvider())->getDependencies();

$mezzioFactories = $mezzioConfig['factories'];
$mezzioAliases   = $mezzioConfig['aliases'];

array_walk($mezzioFactories, function (&$value) {
    $value = factory($value);
});

array_walk($mezzioAliases, function (&$value) {
    $value = get($value);
});

return [

    'definitions' => [

        AppRunnerFactoryInterface::class => autowire(AppRunnerFactory::class),

        WebAppRunnerInterface::class => autowire(MezzioWebAppRunner::class),

        WebConfigInterface::class => autowire(WebConfig::class),

        RouteCollectorInterface::class  => autowire(RouteCollector::class),
        RouterInterface::class          => autowire(FastRouteRouter::class),
        UriFactoryInterface::class      => autowire(UriFactory::class),
        ResponseFactoryInterface::class => autowire(ResponseFactory::class),
        StreamFactoryInterface::class   => autowire(StreamFactory::class),

        ...$mezzioFactories,
        ...$mezzioAliases,

        'Mezzio\Response\ServerRequestErrorResponseGenerator' => autowire(ServerRequestErrorResponseGenerator::class),

        ImplicitHeadMiddleware::class => factory(static function (
            RouterInterface $router,
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

//        RequestIdMiddleware::class => \DI\factory(function () {
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
                    new \GuzzleHttp\MessageFormatter('{req_headers} {req_body} => {res_headers}'),
                    LogLevel::DEBUG
                )
            );

            return new Client([
                'handler'     => $stack,
                'http_errors' => false,
            ]);
        }),

        ClientInterface::class => autowire(\Http\Adapter\Guzzle7\Client::class),

        Context::class => factory(static function (AppEnvInterface $appEnv) {
            $prefix = implode('.', [
                $appEnv->getAppCodename(),
                $appEnv->getRevisionKey(),
                $appEnv->getModeName(),
            ]);

            $factory = new RedisConnectionFactory([
                'scheme_extensions' => ['phpredis'],

                'host'  => getenv('REDIS_HOST'),
                'port'  => getenv('REDIS_PORT'),
                'lazy'  => true,
                'async' => true,

                'predis_options' => [
                    'prefix' => $prefix,
                ],

                'redelivery_delay'        => 300,
                'timeout'                 => 15,
                'read_write_timeout'      => 5,
                'phpredis_retry_interval' => 5,
            ]);

            return $factory->createContext();
        }),

        NamespaceBasedFactoryBuilderInterface::class => autowire(NamespaceBasedFactoryBuilder::class),

        RepositoryFactoryInterface::class => autowire(RepositoryFactory::class),

        UserRepositoryInterface::class        => autowire(UserRepository::class),
        UserStateRepositoryInterface::class   => autowire(UserStateRepository::class),
        UserSessionRepositoryInterface::class => autowire(UserSessionRepository::class),
        RoleRepositoryInterface::class        => autowire(RoleRepository::class),
        TokenRepositoryInterface::class       => autowire(TokenRepository::class),

        IdentityConverterInterface::class => autowire(DummyIdentityConverter::class),

        ParameterProviderFactoryInterface::class => autowire(DefenceParameterProviderFactory::class),

        ApiLanguageDetectorInterface::class => autowire(ApiLanguageDetector::class),

        MessageTargetResolverInterface::class     => autowire(UserMessageTargetResolver::class),
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

        OrmFactoryInterface::class    => autowire(OrmFactory::class),

        // PSR-16 adapter for system-wide PSR-6 cache
        CacheInterface::class         => factory(function (CacheItemPoolInterface $psr6Cache) {
            return new Psr16Cache($psr6Cache);
        }),

        // Common cache instance for all
        CacheItemPoolInterface::class => get(SymfonyCacheProvider::class),

        AppConfigInterface::class                       => autowire(AppConfig::class),

        // Acl roles, resources, permissions and resource factory
        AclRolesCollectorInterface::class               => autowire(AclRolesCollector::class),
        AclResourcesCollectorInterface::class           => autowire(AclResourcesCollector::class),
        AclRulesCollectorInterface::class               => autowire(AclRulesCollector::class),
        AclResourceFactoryInterface::class              => autowire(AclResourceFactory::class),

        // Use Twig as default view
        ViewFactoryInterface::class                     => autowire(TwigViewFactory::class),
        // Use Twig in layouts
        LayoutViewInterface::class                      => autowire(TwigLayoutView::class),

        // Custom access resolver detector
        ApiMethodAccessResolverDetectorInterface::class => autowire(CustomApiMethodAccessResolverDetector::class),

        // Use default renderer for notification messages
        MessageRendererInterface::class                 => autowire(MessageRenderer::class),

        Meta::class => factory(function () {
            throw new LogicException('DI injection of class Meta is deprecated');
        }),

        StaticAssets::class => factory(function () {
            throw new LogicException('DI injection of class StaticAssets is deprecated');
        }),

        BoundedEventTransportInterface::class  => autowire(EsbBoundedEventTransport::class),
        OutboundEventTransportInterface::class => autowire(EsbOutboundEventTransport::class),

        EventBusConfigInterface::class => autowire(EventBusConfig::class),
        EventBusInterface::class       => autowire(EventBus::class),

        // Handlers will be added in workers
        CommandBusInterface::class     => autowire(CommandBus::class),

        ConsoleOptionBuilderInterface::class => autowire(ConsoleOptionBuilder::class),
        ConsoleTaskLocatorInterface::class   => autowire(ConsoleTaskLocator::class),

        DebugBarFactoryInterface::class       => autowire(DebugBarFactory::class),
        DebugBarAccessControlInterface::class => autowire(DebugBarAccessControl::class),
    ],

];
