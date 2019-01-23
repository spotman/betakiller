<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\DebugBarCookiesDataCollector;
use BetaKiller\Dev\DebugBarHttpDriver;
use BetaKiller\Dev\DebugBarSessionDataCollector;
use BetaKiller\Dev\DebugBarUserDataCollector;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\Service\UserService;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\Storage\FileStorage;
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
     * DebugMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \BetaKiller\Helper\CookieHelper            $cookieHelper
     * @param \BetaKiller\Service\UserService            $userService
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param \Psr\Http\Message\StreamFactoryInterface   $streamFactory
     * @param \BetaKiller\Log\LoggerInterface            $logger
     */
    public function __construct(
        AppEnvInterface $appEnv,
        CookieHelper $cookieHelper,
        UserService $userService,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger
    ) {
        $this->responseFactory = $responseFactory;
        $this->cookieHelper    = $cookieHelper;
        $this->userService     = $userService;
        $this->streamFactory   = $streamFactory;
        $this->appEnv          = $appEnv;
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

            if ($this->userService->isDeveloper($user)) {
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

        // Storage for processing data for AJAX calls and redirects
        $debugBar->setStorage(new FileStorage($this->appEnv->getTempPath()));

        // Initialize profiler with DebugBar instance and enable it
        ServerRequestHelper::getProfiler($request)->enable($debugBar);

        // Prepare renderer
        $renderer = $debugBar->getJavascriptRenderer('/phpDebugBar');
        $renderer->setEnableJqueryNoConflict(false); // No jQuery
        $renderer->addInlineAssets([
            '.phpdebugbar-widgets-measure:hover { background: #dcdbdb }',
            '.phpdebugbar-widgets-measure:hover .phpdebugbar-widgets-label { color: #222 !important }',
            '.phpdebugbar-widgets-measure:hover .phpdebugbar-widgets-value { background: #009bda }',
        ], [], []);
        $middleware = new PhpDebugBarMiddleware($renderer, $this->responseFactory, $this->streamFactory);

        $csp = ServerRequestHelper::getCsp($request);

        // DebugBar uses inline tags and images
        $csp->csp('image', 'data:');
        $csp->csp('script', 'unsafe-inline');
        $csp->csp('script', 'unsafe-eval');

        // Forward call
        $response = $middleware->process($request->withAttribute(DebugBar::class, $debugBar), $handler);

        // Add headers injected by DebugBar
        return $httpDriver->applyHeaders($response);
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

        $this->logger->pushHandler($phpConsoleHandler);
    }
}
