<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Assets\Middleware\DownloadMiddleware;
use BetaKiller\Assets\Middleware\OriginalMiddleware;
use BetaKiller\Assets\Middleware\PreviewMiddleware;
use BetaKiller\Assets\Middleware\UploadMiddleware;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Assets\StaticFilesDeployHandler;
use BetaKiller\Middleware\ContentNegotiationMiddleware;
use BetaKiller\Middleware\DebugBarPatchMiddleware;
use BetaKiller\Middleware\DebugMiddleware;
use BetaKiller\Middleware\ErrorPageMiddleware;
use BetaKiller\Middleware\ExpectedExceptionMiddleware;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Middleware\MaintenanceModeMiddleware;
use BetaKiller\Middleware\ProfilerMiddleware;
use BetaKiller\Middleware\SchemeMiddleware;
use BetaKiller\Middleware\SitemapRequestHandler;
use BetaKiller\Middleware\UrlElementDispatchMiddleware;
use BetaKiller\Middleware\UrlElementRenderMiddleware;
use BetaKiller\Middleware\UrlHelperMiddleware;
use BetaKiller\Middleware\UserMiddleware;
use Middlewares\ContentType;
use Psr\Http\Message\ResponseInterface;
use Spotman\Api\ApiRequestHandler;
use Zend\Diactoros\Response\TextResponse;
use Zend\Expressive\Application;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Expressive\Session\SessionMiddleware;

class WebApp
{
    /**
     * @var \Zend\Expressive\Application
     */
    private $app;

    /**
     * WebApp constructor.
     *
     * @param \Zend\Expressive\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
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
        // The error handler should be the first (most outer) middleware to catch
        // all Exceptions.
//        $this->app->pipe(ErrorHandler::class);
//        $this->app->pipe(ServerUrlMiddleware::class);
        // TODO Insert here fallback Exception handler with plain text 500 response

        // Pipe more middleware here that you want to execute on every request:
        // - bootstrapping
        // - pre-conditions
        // - modifications to outgoing responses
        //
        // Piped Middleware may be either callables or service names. Middleware may
        // also be passed as an array; each item in the array must resolve to
        // middleware eventually (i.e., callable or service name).
        //
        // Middleware can be attached to specific paths, allowing you to mix and match
        // applications under a common domain.  The handlers in each middleware
        // attached this way will see a URI with the matched path segment removed.
        //
        // i.e., path of "/api/member/profile" only passes "/member/profile" to $apiMiddleware
        // - $app->pipe('/api', $apiMiddleware);
        // - $app->pipe('/docs', $apiDocMiddleware);
        // - $app->pipe('/files', $filesMiddleware);

        // Profiling and debugging
        $this->app->pipe(DebugMiddleware::class);
        $this->app->pipe(ProfilerMiddleware::class);

        // Main processing pipe
        $this->app->pipe(SchemeMiddleware::class);
//        $this->app->pipe(RequestIdMiddleware::class);

        $this->app->pipe(SessionMiddleware::class);
        $this->app->pipe(DebugBarPatchMiddleware::class);
        $this->app->pipe(UserMiddleware::class);
        $this->app->pipe(ContentNegotiationMiddleware::class);
        $this->app->pipe(ContentType::class);
        $this->app->pipe(I18nMiddleware::class);

        $this->app->pipe(UrlHelperMiddleware::class);
        $this->app->pipe(ErrorPageMiddleware::class);
        $this->app->pipe(ExpectedExceptionMiddleware::class);

        $this->app->pipe(MaintenanceModeMiddleware::class);

        // TODO Check If-Modified-Since and send 304 Not modified

        // TODO Get middleware.pipe config
        // TODO Add all global middleware here

        // Register the routing middleware in the middleware pipeline.
        // This middleware registers the Zend\Expressive\Router\RouteResult request attribute.
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

        // Seed the UrlHelper with the routing results:
//        $app->pipe(UrlHelperMiddleware::class);

        // Add more middleware here that needs to introspect the routing results; this
        // might include:
        //
        // - route-based authentication
        // - route-based validation
        // - etc.

        // Register the dispatch middleware in the middleware pipeline
        $this->app->pipe(DispatchMiddleware::class);

        // At this point, if no Response is returned by any middleware, the
        // NotFoundHandler kicks in; alternately, you can provide other fallback
        // middleware to execute.
        $this->app->pipe(UrlElementDispatchMiddleware::class);
        $this->app->pipe(UrlElementRenderMiddleware::class);
    }

    private function addRoutes(Application $app): void
    {
        $app->get('/sitemap.xml', SitemapRequestHandler::class);

        // Assets
        $extRegexp  = '[a-z]{2,}'; // (jpg|jpeg|gif|png)
        $sizeRegexp = '[0-9]{0,3}'.AssetsModelImageInterface::SIZE_DELIMITER.'[0-9]{0,3}';

        $itemPlace = '{item:.+}';
        $sizePlace = '-{size:'.$sizeRegexp.'}';
        $extPlace  = '.{ext:'.$extRegexp.'}';

        $uploadAction   = AssetsProviderInterface::ACTION_UPLOAD;
        $downloadAction = AssetsProviderInterface::ACTION_DOWNLOAD;
        $originalAction = AssetsProviderInterface::ACTION_ORIGINAL;
        $deleteAction   = AssetsProviderInterface::ACTION_DELETE;
        $previewAction  = ImageAssetsProviderInterface::ACTION_PREVIEW;

        /**
         * Upload file via concrete provider
         *
         * "assets/<provider>/upload"
         */
        $app->post(
            '/assets/{provider}/'.$uploadAction,
            UploadMiddleware::class
        );

        /**
         * Static files legacy route first
         */
        $app->get('/assets/static/{file:.+}', StaticFilesDeployHandler::class);

        /**
         * Download original file via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$downloadAction.$extPlace, DownloadMiddleware::class);

        /**
         * Get original files via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$originalAction.$extPlace, OriginalMiddleware::class);

        /**
         * Preview files via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$previewAction.$sizePlace.$extPlace, PreviewMiddleware::class);

        /**
         * Delete files via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$deleteAction.$sizePlace.$extPlace, PreviewMiddleware::class);

        // API HTTP gate
        $app->post('/api/v{version:\d+}/{type:.+}', ApiRequestHandler::class);
    }

    public function processException(\Throwable $e): ResponseInterface
    {
        $wrap = Exception::wrap($e);

        // TODO Replace with static pretty page + log exception to developers
        return new TextResponse('Error: '.$wrap->oneLiner());
    }
}
