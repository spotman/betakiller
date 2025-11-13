<?php

declare(strict_types=1);

namespace BetaKiller;

use Algorithm\DependencyResolver;
use Algorithm\ResolveBehaviour;
use BetaKiller\Config\WebConfigInterface;
use BetaKiller\Dev\StartupProfiler;
use BetaKiller\Middleware\ContentNegotiationMiddleware;
use BetaKiller\Middleware\FallbackErrorMiddleware;
use BetaKiller\Middleware\PhpBuiltInServerMiddleware;
use BetaKiller\Middleware\ProfilerMiddleware;
use BetaKiller\Middleware\RequestUuidMiddleware;
use BetaKiller\Middleware\ResolvableMiddleware;
use BetaKiller\Middleware\ResolvableMiddlewareFactoryInterface;
use BetaKiller\Middleware\SchemeMiddleware;
use Mezzio\Application;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Middlewares\ContentType;
use Psr\Http\Server\MiddlewareInterface;

final readonly class MezzioWebAppRunner implements WebAppRunnerInterface
{
    public function __construct(
        private Application $app,
        private WebConfigInterface $config,
        private ResolvableMiddlewareFactoryInterface $middlewareFactory
    ) {
    }

    public function run(): void
    {
        // Initialize middleware stack
        $this->addPipeline();

        // Get all routes
        $this->addRoutes($this->app);

        $this->app->run();
    }

    private function addPipeline(): void
    {
        $p = StartupProfiler::getInstance()->start('Configure pipeline');

        // Core middlewares (common for all requests)
        $this->pipeCoreMiddlewares($this->app);

        // Resolve middlewares (common for all requests)
        $this->pipeDynamicMiddlewares($this->app);

        // TODO Check If-Modified-Since and send 304 Not modified

        $this->pipeRoutingMiddlewares($this->app);

        // At this point, if no Response is returned by any middleware, the
        // NotFoundHandler kicks in; alternately, you can provide other fallback
        // middleware to execute.

        $this->app->pipe($this->resolvable($this->config->getNotFoundHandler()));

        StartupProfiler::getInstance()->stop($p);
    }

    private function pipeCoreMiddlewares(Application $app): void
    {
        // The error handler should be the first (most outer) middleware to catch all Exceptions.
        $app->pipe(FallbackErrorMiddleware::class);

        // Generate and bind request ID
        $app->pipe(RequestUuidMiddleware::class);

        // Profiling
        $app->pipe(ProfilerMiddleware::class);

        // Marker header for built-in PHP web-server
        $app->pipe(PhpBuiltInServerMiddleware::class);

        // Check scheme and domain name
        $app->pipe(SchemeMiddleware::class);

        // Prepare request data
        $app->pipe(BodyParamsMiddleware::class);

        // Content negotiation
        $app->pipe(ContentNegotiationMiddleware::class);
        $app->pipe(ContentType::class);
    }

    private function pipeDynamicMiddlewares(Application $app): void
    {
        $pipeConfig = $this->config->getPipeMiddlewares();
        $pipeTable  = [];

        foreach ($pipeConfig as $pipeFqcn) {
            $pipeTable[$pipeFqcn] = $this->config->getMiddlewareDependencies($pipeFqcn);
        }

        // Place first middlewares without dependencies
        uasort($pipeTable, fn(array $a, array $b) => count($a) <=> count($b));

        $behaviour = ResolveBehaviour::create()
            ->setThrowOnMissingReference(true)
            ->setThrowOnCircularReference(true);

        $resolvedPipe = DependencyResolver::resolve($pipeTable, $behaviour);

        foreach ($resolvedPipe as $className) {
            $app->pipe($className);
        }
    }

    private function pipeRoutingMiddlewares(Application $app): void
    {
        // Register the routing middleware in the middleware pipeline.
        $app->pipe(RouteMiddleware::class);

        // The following handle routing failures for common conditions:
        // - HEAD request but no routes answer that method
        // - OPTIONS request but no routes answer that method
        // - method not allowed
        // Order here matters; the MethodNotAllowedMiddleware should be placed
        // after the Implicit*Middleware.
        $app->pipe(ImplicitHeadMiddleware::class);
        $app->pipe(ImplicitOptionsMiddleware::class);
        $app->pipe(MethodNotAllowedMiddleware::class);

        // Register the dispatch middleware in the middleware pipeline
        $app->pipe(DispatchMiddleware::class);
    }

    private function addRoutes(Application $app): void
    {
        $p = StartupProfiler::getInstance()->start('Configure routes');

        foreach ($this->config->fetchGetRoutes() as $path => $handler) {
            $app->get($path, $this->resolvable($handler));
        }

        foreach ($this->config->fetchPostRoutes() as $path => $handler) {
            $app->post($path, $this->resolvable($handler));
        }

        foreach ($this->config->fetchAnyRoutes() as $path => $handler) {
            $app->any($path, $this->resolvable($handler));
        }

        StartupProfiler::getInstance()->stop($p);
    }

    private function resolvable(string $fqcn): MiddlewareInterface
    {
        return new ResolvableMiddleware($this->middlewareFactory, $fqcn);
    }
}
