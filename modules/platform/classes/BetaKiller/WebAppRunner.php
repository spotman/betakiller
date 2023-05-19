<?php
declare(strict_types=1);

namespace BetaKiller;

use Algorithm\DependencyResolver;
use Algorithm\ResolveBehaviour;
use BetaKiller\Assets\Middleware\DeleteMiddleware;
use BetaKiller\Assets\Middleware\DownloadMiddleware;
use BetaKiller\Assets\Middleware\OriginalMiddleware;
use BetaKiller\Assets\Middleware\PreviewMiddleware;
use BetaKiller\Assets\Middleware\UploadInfoMiddleware;
use BetaKiller\Assets\Middleware\UploadMiddleware;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\HasPreviewProviderInterface;
use BetaKiller\Assets\StaticFilesDeployHandler;
use BetaKiller\Config\WebConfigInterface;
use BetaKiller\Dev\StartupProfiler;
use BetaKiller\HitStat\HitStatMiddleware;
use BetaKiller\Middleware\ContentNegotiationMiddleware;
use BetaKiller\Middleware\CustomNotFoundPageMiddleware;
use BetaKiller\Middleware\DummyMiddleware;
use BetaKiller\Middleware\FallbackErrorMiddleware;
use BetaKiller\Middleware\PhpBuiltInServerMiddleware;
use BetaKiller\Middleware\ProfilerMiddleware;
use BetaKiller\Middleware\RequestUuidMiddleware;
use BetaKiller\Middleware\SchemeMiddleware;
use BetaKiller\Middleware\SitemapRequestHandler;
use BetaKiller\Middleware\UrlElementDispatchMiddleware;
use BetaKiller\Middleware\UrlElementRenderMiddleware;
use BetaKiller\Middleware\UrlHelperMiddleware;
use BetaKiller\Middleware\UserStatusMiddleware;
use BetaKiller\RequestHandler\App\I18next\AddMissingTranslationRequestHandler;
use BetaKiller\RequestHandler\App\I18next\FetchTranslationRequestHandler;
use BetaKiller\RobotsTxt\RobotsTxtHandler;
use BetaKiller\Security\CspReportHandler;
use Mezzio\Application;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Middlewares\ContentType;
use Spotman\Api\ApiRequestHandler;

final class WebAppRunner implements AppRunnerInterface
{
    /**
     * @var \Mezzio\Application
     */
    private Application $app;

    /**
     * @var \BetaKiller\Config\WebConfigInterface
     */
    private WebConfigInterface $config;

    /**
     * WebApp constructor.
     *
     * @param \Mezzio\Application                   $app
     * @param \BetaKiller\Config\WebConfigInterface $config
     */
    public function __construct(Application $app, WebConfigInterface $config)
    {
        $this->app    = $app;
        $this->config = $config;
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

        // The error handler should be the first (most outer) middleware to catch all Exceptions.
        $this->app->pipe(FallbackErrorMiddleware::class);

        // Generate and bind request ID
        $this->app->pipe(RequestUuidMiddleware::class);

        // Profiling
        $this->app->pipe(ProfilerMiddleware::class);

        // Marker header for built-in PHP web-server
        $this->app->pipe(PhpBuiltInServerMiddleware::class);

        // Check scheme and domain name
        $this->app->pipe(SchemeMiddleware::class);

        // Prepare request data
        $this->app->pipe(BodyParamsMiddleware::class);

        // Content negotiation
        $this->app->pipe(ContentNegotiationMiddleware::class);
        $this->app->pipe(ContentType::class);


        $config = $this->config->getMiddlewares();

        $behaviour = ResolveBehaviour::create()
            ->setThrowOnMissingReference(true)
            ->setThrowOnCircularReference(true);

        $resolved = DependencyResolver::resolve($config, $behaviour);

        foreach ($resolved as $className) {
            $this->app->pipe($className);
        }

        // TODO Check If-Modified-Since and send 304 Not modified

        // Register the routing middleware in the middleware pipeline.
        $this->app->pipe(RouteMiddleware::class);

        // The following handle routing failures for common conditions:
        // - HEAD request but no routes answer that method
        // - OPTIONS request but no routes answer that method
        // - method not allowed
        // Order here matters; the MethodNotAllowedMiddleware should be placed
        // after the Implicit*Middleware.
        $this->app->pipe(ImplicitHeadMiddleware::class);
        $this->app->pipe(ImplicitOptionsMiddleware::class);
        $this->app->pipe(MethodNotAllowedMiddleware::class);

        // Register the dispatch middleware in the middleware pipeline
        $this->app->pipe(DispatchMiddleware::class);

        // At this point, if no Response is returned by any middleware, the
        // NotFoundHandler kicks in; alternately, you can provide other fallback
        // middleware to execute.

        StartupProfiler::getInstance()->stop($p);
    }

    private function addRoutes(Application $app): void
    {
        $p = StartupProfiler::getInstance()->start('Configure routes');

        foreach ($this->config->fetchGetRoutes() as $path => $handler) {
            $app->get($path, $handler);
        }

        foreach ($this->config->fetchPostRoutes() as $path => $handler) {
            $app->post($path, $handler);
        }

        foreach ($this->config->fetchAnyRoutes() as $path => $handler) {
            $app->any($path, $handler);
        }

        StartupProfiler::getInstance()->stop($p);
    }
}
