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
use BetaKiller\Assets\Provider\HasPreviewProviderInterface;
use BetaKiller\Assets\StaticFilesDeployHandler;
use BetaKiller\Dev\UserDebugMiddleware;
use BetaKiller\HitStat\HitStatMiddleware;
use BetaKiller\Middleware\ContentNegotiationMiddleware;
use BetaKiller\Middleware\CustomNotFoundPageMiddleware;
use BetaKiller\Middleware\DebugMiddleware;
use BetaKiller\Middleware\ErrorPageMiddleware;
use BetaKiller\Middleware\ExpectedExceptionMiddleware;
use BetaKiller\Middleware\FallbackErrorMiddleware;
use BetaKiller\Middleware\I18nMiddleware;
use BetaKiller\Middleware\MaintenanceModeMiddleware;
use BetaKiller\Middleware\PhpBuiltInServerMiddleware;
use BetaKiller\Middleware\ProfilerMiddleware;
use BetaKiller\Middleware\RequestUserMiddleware;
use BetaKiller\Middleware\RequestUuidMiddleware;
use BetaKiller\Middleware\SchemeMiddleware;
use BetaKiller\Middleware\SessionMiddleware;
use BetaKiller\Middleware\SitemapRequestHandler;
use BetaKiller\Middleware\UrlElementDispatchMiddleware;
use BetaKiller\Middleware\UrlElementRenderMiddleware;
use BetaKiller\Middleware\UrlHelperMiddleware;
use BetaKiller\Middleware\UserLanguageMiddleware;
use BetaKiller\Middleware\UserStatusMiddleware;
use BetaKiller\Middleware\WampCookieMiddleware;
use BetaKiller\RequestHandler\App\I18next\AddMissingTranslationRequestHandler;
use BetaKiller\RequestHandler\App\I18next\FetchTranslationRequestHandler;
use BetaKiller\RobotsTxt\RobotsTxtHandler;
use BetaKiller\Security\CspReportHandler;
use BetaKiller\Security\SecureHeadersMiddleware;
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
     * WebApp constructor.
     *
     * @param \Mezzio\Application $app
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

        // Profiling
        $this->app->pipe(ProfilerMiddleware::class);

        // Marker header for built-in PHP web-server
        $this->app->pipe(PhpBuiltInServerMiddleware::class);

        // Generate and bind request ID
        $this->app->pipe(RequestUuidMiddleware::class);

        // Prepare request data
        $this->app->pipe(BodyParamsMiddleware::class);
        $this->app->pipe(SchemeMiddleware::class);
        $this->app->pipe(SecureHeadersMiddleware::class);

        // Fetch Session
        $this->app->pipe(SessionMiddleware::class);

        // Bind RequestUserProvider
        $this->app->pipe(RequestUserMiddleware::class);

        // Debugging (depends on session)
        $this->app->pipe(DebugMiddleware::class);

        // I18n and content negotiation
        $this->app->pipe(ContentNegotiationMiddleware::class);
        $this->app->pipe(ContentType::class);
        $this->app->pipe(I18nMiddleware::class);

        // Exceptions handling (depends on i18n)
        $this->app->pipe(ErrorPageMiddleware::class);
        $this->app->pipe(ExpectedExceptionMiddleware::class);

        // Throws raw 501 exception, proceeded by ErrorPageMiddleware
        $this->app->pipe(MaintenanceModeMiddleware::class);

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
    }

    private function addRoutes(Application $app): void
    {
        $userPipe = [
            // Depends on user and i18n
            UserLanguageMiddleware::class,

            // Add debug info
            UserDebugMiddleware::class,
        ];

        $app->post(CspReportHandler::URL, [
            ...$userPipe,
            CspReportHandler::class,
        ], 'security-csp-handler');

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
        $previewAction  = HasPreviewProviderInterface::ACTION_PREVIEW;

        $uploadUrl = '/assets/{provider}/'.$uploadAction;

        /**
         * Get upload info and restrictions
         */
        $app->get(
            $uploadUrl,
            [
                ...$userPipe,
                UploadInfoMiddleware::class,
            ],
            'assets-upload-info'
        );

        /**
         * Upload file via concrete provider
         *
         * "assets/<provider>/upload"
         */
        $app->post(
            $uploadUrl,
            [
                ...$userPipe,
                UploadMiddleware::class,
            ],
            'assets-upload'
        );

        /**
         * Static files legacy route first
         */
        $app->get('/assets/static/{file:.+}', StaticFilesDeployHandler::class, 'assets-static');

        /**
         * Download original file via concrete provider
         */
        $app->get(
            '/assets/{provider}/'.$itemPlace.'/'.$downloadAction.$extPlace,
            [
                ...$userPipe,
                DownloadMiddleware::class,
            ],
            'assets-download'
        );

        /**
         * Get original files via concrete provider
         */
        $app->get(
            '/assets/{provider}/'.$itemPlace.'/'.$originalAction.$extPlace,
            [
                ...$userPipe,
                OriginalMiddleware::class,
            ],
            'assets-original'
        );

        /**
         * Preview files via concrete provider
         */
        $app->get(
            '/assets/{provider}/'.$itemPlace.'/'.$previewAction.$sizePlace.$extPlace,
            [
                ...$userPipe,
                PreviewMiddleware::class,
            ],
            'assets-preview'
        );

        /**
         * Delete files via concrete provider
         */
        $app->get(
            '/assets/{provider}/'.$itemPlace.'/'.$deleteAction.$extPlace,
            [
                ...$userPipe,
                DeleteMiddleware::class,
            ],
            'assets-delete'
        );

        // API HTTP gate
        $app->post(
            '/api/v{version:\d+}/{type:.+}',
            [
                ...$userPipe,
                ApiRequestHandler::class,
            ],
            'api-gate'
        );

        // I18n handlers
        $app->get('/i18n/{lang}', FetchTranslationRequestHandler::class, 'i18n-fetch');
        $app->post('/i18n/{lang}/add-missing', AddMissingTranslationRequestHandler::class, 'i18n-add-missing');

        // UrlElement processing
        $urlElementPipe = [
            ...$userPipe,

            // Flash messages for Post-Redirect-Get flow (requires Session)
            FlashMessageMiddleware::class,

            // Heavy operation
            UrlHelperMiddleware::class,

            // Save stat (referrer, target, utm markers, etc) (depends on UrlContainer)
            HitStatMiddleware::class,

            // Display custom 404 page for dispatched UrlElement
            CustomNotFoundPageMiddleware::class,

            // Depends on UrlHelper
            UrlElementDispatchMiddleware::class,

            // Prevent access for locked users
            UserStatusMiddleware::class,

            // Render UrlElement
            UrlElementRenderMiddleware::class,
        ];

        $app->any('/{path:.*}', $urlElementPipe, 'url-element');
    }
}
