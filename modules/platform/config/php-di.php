<?php

use BetaKiller\Acl\EntityPermissionResolver;
use BetaKiller\Acl\EntityPermissionResolverInterface;
use BetaKiller\Acl\UrlElementAccessResolver;
use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\Api\ApiLanguageDetector;
use BetaKiller\DefenceParameterProviderFactory;
use BetaKiller\DummyIdentityConverter;
use BetaKiller\Exception;
use BetaKiller\Factory\EntityFactory;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\RequestLanguageHelperInterface;
use BetaKiller\IdentityConverterInterface;
use BetaKiller\MessageBus\EventSerializerInterface;
use BetaKiller\MessageBus\NativeEventSerializer;
use BetaKiller\Middleware\CspReportBodyParamsStrategy;
use BetaKiller\Notification\MessageActionUrlGeneratorInterface;
use BetaKiller\Notification\ScheduleTargetSpecInterface;
use BetaKiller\Notification\UserScheduleTargetSpec;
use BetaKiller\NotificationMessageActionUrlGenerator;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\TokenRepository;
use BetaKiller\Repository\TokenRepositoryInterface;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use Enqueue\Redis\RedisConnectionFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Http\Client\HttpClient;
use Interop\Queue\Context;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Spotman\Api\ApiLanguageDetectorInterface;
use Spotman\Defence\Parameter\ParameterProviderFactoryInterface;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UriFactory;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Session\SessionPersistenceInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\MiddlewarePipeInterface;
use function DI\autowire;
use function DI\factory;
use function DI\get;

$deprecatedFactory = factory(static function () {
    throw new LogicException('Deprecated');
});

return [

    'definitions' => [

        RouterInterface::class          => autowire(FastRouteRouter::class),
        EmitterInterface::class         => autowire(SapiStreamEmitter::class),
        MiddlewarePipeInterface::class  => autowire(MiddlewarePipe::class),
        RequestFactoryInterface::class  => autowire(RequestFactory::class),
        ResponseFactoryInterface::class => autowire(ResponseFactory::class),
        StreamFactoryInterface::class   => autowire(StreamFactory::class),
        UriFactoryInterface::class      => autowire(UriFactory::class),

        RequestHandlerRunner::class => factory(static function (
            MiddlewarePipeInterface $pipe,
            EmitterInterface $emitter,
            LoggerInterface $logger
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

        HttpClient::class => autowire(\Http\Adapter\Guzzle6\Client::class),

        ClientInterface::class => get(HttpClient::class),

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
            ]);

            return $factory->createContext();
        }),

        UserRepositoryInterface::class  => autowire(UserRepository::class),
        RoleRepositoryInterface::class  => autowire(RoleRepository::class),
        TokenRepositoryInterface::class => autowire(TokenRepository::class),

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

    ],

];
