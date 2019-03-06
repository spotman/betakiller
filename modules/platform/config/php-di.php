<?php

use BetaKiller\Exception;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Log\Logger;
use BetaKiller\Middleware\CspReportBodyParamsStrategy;
use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ResponseFactory;
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

$deprecatedFactory = \DI\factory(function () {
    throw new LogicException('Deprecated');
});

return [

    'definitions' => [

        RouterInterface::class          => \DI\autowire(FastRouteRouter::class),
        EmitterInterface::class         => \DI\autowire(SapiStreamEmitter::class),
        MiddlewarePipeInterface::class  => \DI\autowire(MiddlewarePipe::class),
        RequestFactoryInterface::class  => \DI\autowire(RequestFactory::class),
        ResponseFactoryInterface::class => \DI\autowire(ResponseFactory::class),
        StreamFactoryInterface::class   => \DI\autowire(StreamFactory::class),
        UriFactoryInterface::class      => \DI\autowire(UriFactory::class),

        RequestHandlerRunner::class => \DI\factory(function (
            MiddlewarePipeInterface $pipe,
            EmitterInterface $emitter,
            LoggerInterface $logger
        ) {
            return new RequestHandlerRunner(
                $pipe,
                $emitter,
                function () {
                    return \Zend\Diactoros\ServerRequestFactory::fromGlobals();
                },
                function (\Throwable $e) use ($logger) {
                    // Log exception to developers
                    $logger->alert(Exception::oneLiner($e), [
                        Logger::CONTEXT_KEY_EXCEPTION => $e,
                    ]);

                    // No exception info here for security reasons
                    return new TextResponse('System error', 500);
                }
            );
        }),

        ImplicitHeadMiddleware::class => \DI\factory(function (
            RouterInterface $router,
            StreamFactoryInterface $factory
        ) {
            return new ImplicitHeadMiddleware($router, function () use ($factory) {
                return $factory->createStream();
            });
        }),

        ImplicitOptionsMiddleware::class => \DI\factory(function (ResponseFactoryInterface $factory) {
            return new ImplicitOptionsMiddleware(function () use ($factory) {
                return $factory->createResponse();
            });
        }),

        MethodNotAllowedMiddleware::class => \DI\factory(function (ResponseFactoryInterface $factory) {
            return new MethodNotAllowedMiddleware(function () use ($factory) {
                return $factory->createResponse();
            });
        }),

//        RequestIdMiddleware::class => DI\factory(function () {
//            $generator         = new \PhpMiddleware\RequestId\Generator\PhpUniqidGenerator();
//            $requestIdProvider = new \PhpMiddleware\RequestId\RequestIdProviderFactory($generator);
//
//            return new RequestIdMiddleware($requestIdProvider);
//        }),

        BodyParamsMiddleware::class => DI\factory(function () {
            $params = new BodyParamsMiddleware();

            $params->addStrategy(new CspReportBodyParamsStrategy());

            return $params;
        }),

        SessionStorageInterface::class     => DI\autowire(DatabaseSessionStorage::class),
        SessionPersistenceInterface::class => DI\get(SessionStorageInterface::class),

        // Deprecated DI objects
        // UrlHelper is used via Container::make() method and can not be deprecated
        UrlElementStack::class             => \DI\factory(function () {
            throw new LogicException(UrlElementStack::class.' DI injection deprecated, use ServerRequestHelper::getUrlElementStack()');
        }),

        UrlContainerInterface::class => \DI\factory(function () {
            throw new LogicException(UrlContainerInterface::class.' DI injection deprecated, use ServerRequestHelper::getUrlContainer() instead');
        }),

        I18nHelper::class => \DI\factory(function () {
            throw new Exception(I18nHelper::class.' DI injection deprecated, use ServerRequestHelper::getI18n() instead');
        }),
    ],

];
