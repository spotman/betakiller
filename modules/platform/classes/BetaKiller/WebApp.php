<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Assets\Middleware\DeleteMiddleware;
use BetaKiller\Assets\Middleware\DownloadMiddleware;
use BetaKiller\Assets\Middleware\OriginalMiddleware;
use BetaKiller\Assets\Middleware\PreviewMiddleware;
use BetaKiller\Assets\Middleware\UploadInfoMiddleware;
use BetaKiller\Assets\Middleware\UploadMiddleware;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Assets\StaticFilesDeployHandler;
use BetaKiller\HitStat\HitStatMiddleware;
use BetaKiller\Middleware\ContentNegotiationMiddleware;
use BetaKiller\Middleware\CustomNotFoundPageMiddleware;
use BetaKiller\Middleware\DebugMiddleware;
use BetaKiller\Middleware\ErrorPageMiddleware;
use BetaKiller\Middleware\ExpectedExceptionMiddleware;
use BetaKiller\Middleware\FallbackErrorMiddleware;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Middleware\MaintenanceModeMiddleware;
use BetaKiller\Middleware\ProfilerMiddleware;
use BetaKiller\Middleware\RequestUuidMiddleware;
use BetaKiller\Middleware\SchemeMiddleware;
use BetaKiller\Middleware\SessionMiddleware;
use BetaKiller\Middleware\SitemapRequestHandler;
use BetaKiller\Middleware\UrlElementDispatchMiddleware;
use BetaKiller\Middleware\UrlElementRenderMiddleware;
use BetaKiller\Middleware\UrlHelperMiddleware;
use BetaKiller\Middleware\UserLanguageMiddleware;
use BetaKiller\Middleware\UserMiddleware;
use BetaKiller\Middleware\UserStatusMiddleware;
use BetaKiller\Middleware\WampCookieMiddleware;
use BetaKiller\RequestHandler\App\I18next\AddMissingTranslationRequestHandler;
use BetaKiller\RequestHandler\App\I18next\FetchTranslationRequestHandler;
use BetaKiller\RobotsTxt\RobotsTxtHandler;
use BetaKiller\Security\CspReportHandler;
use BetaKiller\Security\SecureHeadersMiddleware;
use Middlewares\ContentType;
use Spotman\Api\ApiRequestHandler;
use Zend\Expressive\Application;
use Zend\Expressive\Flash\FlashMessageMiddleware;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;

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
        // The error handler should be the first (most outer) middleware to catch all Exceptions.
        $this->app->pipe(FallbackErrorMiddleware::class);

        // Generate and bind request ID
        $this->app->pipe(RequestUuidMiddleware::class);

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

        // Profiling
        $this->app->pipe(ProfilerMiddleware::class);

        // Main processing pipe
        $this->app->pipe(BodyParamsMiddleware::class);
        $this->app->pipe(SchemeMiddleware::class);
        $this->app->pipe(SecureHeadersMiddleware::class);
//        $this->app->pipe(RequestIdMiddleware::class);

        // I18n and content negotiation
        $this->app->pipe(ContentNegotiationMiddleware::class);
        $this->app->pipe(ContentType::class);
        $this->app->pipe(I18nMiddleware::class);

        // Common processing
        $this->app->pipe(WampCookieMiddleware::class);

        // Auth
        $this->app->pipe(SessionMiddleware::class);
        $this->app->pipe(UserMiddleware::class);

        // Depends on user and i18n
        $this->app->pipe(UserLanguageMiddleware::class);

        // Debugging (depends on session and per-user debug mode)
        $this->app->pipe(DebugMiddleware::class);

        // Exceptions handling (depends on i18n)
        $this->app->pipe(ErrorPageMiddleware::class);
        $this->app->pipe(ExpectedExceptionMiddleware::class);

        // Throws raw 501 exception, proceeded by ErrorPageMiddleware
        $this->app->pipe(MaintenanceModeMiddleware::class);

        // Flash messages for Post-Redirect-Get flow (requires Session)
        $this->app->pipe(FlashMessageMiddleware::class);

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

        // Heavy operation, defer
        $this->app->pipe(UrlHelperMiddleware::class);

        // Save stat (referrer, target, utm markers, etc) (depends on UrlContainer)
        $this->app->pipe(HitStatMiddleware::class);

        // Display custom 404 page for dispatched UrlElement
        $this->app->pipe(CustomNotFoundPageMiddleware::class);

        // Depends on UrlHelper
        $this->app->pipe(UrlElementDispatchMiddleware::class);

        // Prevent access for locked users
        $this->app->pipe(UserStatusMiddleware::class);

        // Render UrlElement
        $this->app->pipe(UrlElementRenderMiddleware::class);
    }

    private function addRoutes(Application $app): void
    {
        $app->post(CspReportHandler::URL, CspReportHandler::class, 'security-csp-handler');

        $app->get('/sitemap.xml', SitemapRequestHandler::class, 'sitemap');
        $app->get('/robots.txt', RobotsTxtHandler::class, 'robots.txt');

        // Assets
        $extRegexp  = '[a-z]{2,}'; // (jpg|jpeg|gif|png)
        $sizeRegexp = '[0-9]{0,4}'.AssetsModelImageInterface::SIZE_DELIMITER.'[0-9]{0,4}';

        $itemPlace = '{item:.+}';
        $sizePlace = '-{size:'.$sizeRegexp.'}';
        $extPlace  = '.{ext:'.$extRegexp.'}';

        $uploadAction   = AssetsProviderInterface::ACTION_UPLOAD;
        $downloadAction = AssetsProviderInterface::ACTION_DOWNLOAD;
        $originalAction = AssetsProviderInterface::ACTION_ORIGINAL;
        $deleteAction   = AssetsProviderInterface::ACTION_DELETE;
        $previewAction  = ImageAssetsProviderInterface::ACTION_PREVIEW;

        $uploadUrl = '/assets/{provider}/'.$uploadAction;

        /**
         * Get upload info and restrictions
         */
        $app->get(
            $uploadUrl,
            UploadInfoMiddleware::class,
            'assets-upload-info'
        );

        /**
         * Upload file via concrete provider
         *
         * "assets/<provider>/upload"
         */
        $app->post(
            $uploadUrl,
            UploadMiddleware::class,
            'assets-upload'
        );

        /**
         * Static files legacy route first
         */
        $app->get('/assets/static/{file:.+}', StaticFilesDeployHandler::class, 'assets-static');

        /**
         * Download original file via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$downloadAction.$extPlace, DownloadMiddleware::class,
            'assets-download');

        /**
         * Get original files via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$originalAction.$extPlace, OriginalMiddleware::class,
            'assets-original');

        /**
         * Preview files via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$previewAction.$sizePlace.$extPlace, PreviewMiddleware::class,
            'assets-preview');

        /**
         * Delete files via concrete provider
         */
        $app->get('/assets/{provider}/'.$itemPlace.'/'.$deleteAction.$extPlace, DeleteMiddleware::class,
            'assets-delete');

        // API HTTP gate
        $app->post('/api/v{version:\d+}/{type:.+}', ApiRequestHandler::class, 'api-gate');

        // I18n handlers
        $app->get('/i18n/{lang}', FetchTranslationRequestHandler::class, 'i18n-fetch');
        $app->post('/i18n/{lang}/add-missing', AddMissingTranslationRequestHandler::class, 'i18n-add-missing');
    }
}
