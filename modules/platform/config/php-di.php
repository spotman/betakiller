<?php

use BetaKiller\Exception;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UriFactory;
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
            EmitterInterface $emitter
        ) {
            return new RequestHandlerRunner(
                $pipe,
                $emitter,
                function () {
                    return \Zend\Diactoros\ServerRequestFactory::fromGlobals();
                },
                function (\Throwable $e) {
                    // TODO Replace with static pretty page + log exception to developers
                    return new TextResponse('Error: '.Exception::oneLiner($e).PHP_EOL.PHP_EOL.$e->getTraceAsString());
                }
            );
        }),

        ImplicitHeadMiddleware::class => \DI\factory(function (
            RouterInterface $router,
            StreamFactoryInterface $factory
        ) {
            return new ImplicitHeadMiddleware($router, function () use ($factory) {
                return $factory;
            });
        }),

        ImplicitOptionsMiddleware::class => \DI\factory(function (ResponseFactoryInterface $factory) {
            return new ImplicitOptionsMiddleware(function () use ($factory) {
                return $factory;
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
