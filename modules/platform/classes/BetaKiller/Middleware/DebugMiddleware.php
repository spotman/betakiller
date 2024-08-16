<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\DebugBarCookiesDataCollector;
use BetaKiller\Dev\DebugBarHttpDriver;
use BetaKiller\Dev\DebugBarSessionDataCollector;
use BetaKiller\Dev\DebugBarTimeDataCollector;
use BetaKiller\Dev\DebugBarTwigDataCollector;
use BetaKiller\Dev\DebugBarUserDataCollector;
use BetaKiller\Dev\DebugServerRequestHelper;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Log\LoggerInterface;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer;
use DebugBar\Storage\FileStorage;
use PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;

final class DebugMiddleware implements MiddlewareInterface
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
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Twig\Environment
     */
    private $twigEnv;

    /**
     * DebugMiddleware constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface            $appEnv
     * @param \Twig\Environment                          $twigEnv
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param \Psr\Http\Message\StreamFactoryInterface   $streamFactory
     * @param \BetaKiller\Log\LoggerInterface            $logger
     */
    public function __construct(
        AppEnvInterface          $appEnv,
        Environment              $twigEnv,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        LoggerInterface          $logger
    ) {
        $this->responseFactory = $responseFactory;
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

        // Fetch actual session
        $session = ServerRequestHelper::getSession($request);

        // Detect debug mode enabled for session
        // Do not fetch User here to allow lazy loading
        if (SessionHelper::isDebugEnabled($session)) {
            $debugEnabled = true;
        }

        // Prevent displaying DebugBar in prod mode
        if (!$debugEnabled || $this->appEnv->inProductionMode()) {
            // Forward call
            return $handler->handle($request);
        }

        $ps = RequestProfiler::begin($request, 'Debug middleware (start up)');

        // Fresh instance for every request
        $debugBar = new DebugBar();

        // Initialize http driver
        $httpDriver = new DebugBarHttpDriver($session);
        $debugBar->setHttpDriver($httpDriver);

        $debugBar
            ->addCollector(new DebugBarTimeDataCollector($request))
            ->addCollector(new DebugBarCookiesDataCollector($request))
            ->addCollector(new DebugBarSessionDataCollector($session))
            ->addCollector(new DebugBarUserDataCollector($request))
            ->addCollector(new MemoryCollector())
            ->addCollector(new MonologCollector($this->logger->getMonologInstance()));

        if (ServerRequestHelper::isHtmlPreferred($request)) {
            $debugBar->addCollector(new DebugBarTwigDataCollector($this->twigEnv));
        }

        // Storage for processing data for AJAX calls and redirects
        $debugBar->setStorage(new FileStorage($this->appEnv->getTempPath('debugbar-storage')));

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

        // Inject DebugBar instance
        $request = DebugServerRequestHelper::withDebugBar($request, $debugBar);

        // Stop profiler before call forward
        RequestProfiler::end($ps);

        // Forward call
        $response = $middleware->process($request, $handler);

        // DebugBar generates inline tags and images so configuring CSP
        $this->addCspRules($renderer, $request);

        // Add headers injected by DebugBar
        return $httpDriver->applyHeaders($response);
    }

    private function addCspRules(JavascriptRenderer $renderer, ServerRequestInterface $request): void
    {
        $csp = ServerRequestHelper::getCsp($request);

        if (!$csp) {
            return;
        }

        $inlineJs  = $renderer->getAssets('inline_js');
        $inlineCss = $renderer->getAssets('inline_css');
        $initJs    = str_replace(['<script type="text/javascript">', '</script>'], '', trim($renderer->render()));

        foreach ($inlineJs as $js) {
            $csp->cspHash('script', $js);
        }

        // Temporary disable coz 'unsafe-inline' for styles enabled (pain in the ass with third-party widgets)
        foreach ($inlineCss as $css) {
            $csp->cspHash('style', $css);
        }

        $csp->cspHash('script', $initJs);

        // Inline images in PhpDebugBar
        $csp->csp('image', 'data:');
    }
}
