<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use Aidantwoods\SecureHeaders\SecureHeaders;
use BetaKiller\Dev\DebugBarCookiesDataCollector;
use BetaKiller\Dev\DebugBarHttpDriver;
use BetaKiller\Dev\DebugBarSessionDataCollector;
use BetaKiller\Dev\DebugBarUserDataCollector;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Log\FilterExceptionsHandler;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\Service\UserService;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer;
use Monolog\Handler\PHPConsoleHandler;
use PhpConsole\Connector;
use PhpConsole\Storage\File;
use PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;

class DebugMiddleware implements MiddlewareInterface
{
    /**
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookieHelper;

    /**
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * @var \Twig\Environment
     */
    private $twigEnv;

    /**
     * DebugMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \BetaKiller\Helper\CookieHelper            $cookieHelper
     * @param \BetaKiller\Service\UserService            $userService
     * @param \Twig\Environment                          $twigEnv
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param \Psr\Http\Message\StreamFactoryInterface   $streamFactory
     * @param \BetaKiller\Log\LoggerInterface            $logger
     */
    public function __construct(
        AppEnvInterface $appEnv,
        CookieHelper $cookieHelper,
        UserService $userService,
        Environment $twigEnv,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger
    ) {
        $this->responseFactory = $responseFactory;
        $this->cookieHelper    = $cookieHelper;
        $this->userService     = $userService;
        $this->streamFactory   = $streamFactory;
        $this->appEnv          = $appEnv;
        $this->twigEnv         = $twigEnv;
        $this->logger          = $logger;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \DebugBar\DebugBarException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $debugEnabled = $this->appEnv->isDebugEnabled();

        // TODO Detect debug mode enabled for session
        if (!$debugEnabled && ServerRequestHelper::hasUser($request)) {
            $user = ServerRequestHelper::getUser($request);

            if ($user->isDeveloper()) {
                $debugEnabled = true;
            }
        }

        if (!$debugEnabled) {
            // Forward call
            return $handler->handle($request);
        }

        // Enable debugging via PhpConsole
        if ($this->appEnv->inDevelopmentMode() && $this->isPhpConsoleActive()) {
            $this->initPhpConsole();
        }

        $startTime = $request->getServerParams()['REQUEST_TIME_FLOAT'] ?? null;

        // Fresh instance for every request
        $debugBar = new DebugBar();

        // Fetch actual session
        $session = ServerRequestHelper::getSession($request);

        // Fetch actual user
        $user = ServerRequestHelper::getUser($request);

        // Initialize http driver
        $httpDriver = new DebugBarHttpDriver($session);
        $debugBar->setHttpDriver($httpDriver);

        $debugBar
            ->addCollector(new TimeDataCollector($startTime))
            ->addCollector(new DebugBarUserDataCollector($user))
            ->addCollector(new DebugBarSessionDataCollector($session))
            ->addCollector(new DebugBarCookiesDataCollector($this->cookieHelper, $request))
            ->addCollector(new MemoryCollector());

// Temp disable coz of error
//        if (ServerRequestHelper::isHtmlPreferred($request)) {
//            $debugBar->addCollector(new DebugBarTwigDataCollector($debugBar, $this->twigEnv));
//        }

        // Temporary disable storage for testing purposes
        // Storage for processing data for AJAX calls and redirects
        // $debugBar->setStorage(new FileStorage($this->appEnv->getTempPath()));

        // Initialize profiler with DebugBar instance and enable it
        ServerRequestHelper::getProfiler($request)->enable($debugBar);

        // Prepare renderer
        $renderer = $debugBar->getJavascriptRenderer('/phpDebugBar');
        $renderer->setEnableJqueryNoConflict(false); // No jQuery
        $renderer->addInlineAssets([
            '.phpdebugbar-widgets-measure:hover { background: #dcdbdb }'.
            '.phpdebugbar-widgets-measure:hover .phpdebugbar-widgets-label { color: #222 !important }'.
            '.phpdebugbar-widgets-measure:hover .phpdebugbar-widgets-value { background: #009bda }'.
            'div.phpdebugbar-header, a.phpdebugbar-restore-btn { background: #efefef }'.
            'div.phpdebugbar-header { padding-left: 0 }'.
            'a.phpdebugbar-restore-btn { text-align: center }'.
            'a.phpdebugbar-restore-btn:before { content: "{}"; font-size: 16px; color: #333; font-weight: bold }',
        ], [], []);
        $middleware = new PhpDebugBarMiddleware($renderer, $this->responseFactory, $this->streamFactory);

        // Forward call
        $response = $middleware->process($request->withAttribute(DebugBar::class, $debugBar), $handler);

        // DebugBar generates inline tags and images so configuring CSP
        $csp = ServerRequestHelper::getCsp($request);
        $this->addCspRules($renderer, $csp);

        // Prevent caching
        $response = ResponseHelper::disableCaching($response);

        // Add headers injected by DebugBar
        return $httpDriver->applyHeaders($response);
    }

    private function addCspRules(JavascriptRenderer $renderer, SecureHeaders $csp): void
    {
        $inlineJs  = $renderer->getAssets('inline_js');
        $inlineCss = $renderer->getAssets('inline_css');
        $initJs    = \str_replace(['<script type="text/javascript">', '</script>'], '', \trim($renderer->render()));

        foreach ($inlineJs as $js) {
            $csp->cspHash('script', $js);
        }

        foreach ($inlineCss as $css) {
            $csp->cspHash('style', $css);
        }

        $csp->cspHash('script', $initJs);

        // Inline images in PhpDebugBar
        $csp->csp('image', 'data:');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isPhpConsoleActive(): bool
    {
        $storageFileName = $this->appEnv->getModeName().'.'.$this->appEnv->getRevisionKey().'.phpConsole.data';
        $storagePath     = $this->appEnv->getTempPath().DIRECTORY_SEPARATOR.$storageFileName;

        // Can be called only before PhpConsole\Connector::getInstance() and PhpConsole\Handler::getInstance()
        Connector::setPostponeStorage(new File($storagePath));

        return Connector::getInstance()->isActiveClient();
    }

    /**
     * @throws \Exception
     */
    private function initPhpConsole(): void
    {
        $phpConsoleHandler = new PHPConsoleHandler([
            'enableSslOnlyMode'        => true,
            'detectDumpTraceAndSource' => true,     // Autodetect and append trace data to debug
            'useOwnErrorsHandler'      => false,    // Enable errors handling
            'useOwnExceptionsHandler'  => false,    // Enable exceptions handling
        ]);

//        $phpConsoleHandler->pushProcessor(new ContextCleanupProcessor);

        $this->logger->pushHandler(new FilterExceptionsHandler($phpConsoleHandler));
    }
}
