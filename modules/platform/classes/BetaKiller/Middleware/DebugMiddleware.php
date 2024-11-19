<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\DebugBarAccessControlInterface;
use BetaKiller\Dev\DebugBarFactoryInterface;
use BetaKiller\Dev\DebugServerRequestHelper;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use DebugBar\JavascriptRenderer;
use DebugBar\OpenHandler;
use Laminas\Diactoros\Response\TextResponse;
use PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class DebugMiddleware implements MiddlewareInterface
{
    public const BASE_URL = '/phpDebugBar';

    /**
     * DebugMiddleware constructor.
     *
     * @param \BetaKiller\Dev\DebugBarAccessControlInterface $accessControl
     * @param \Psr\Http\Message\ResponseFactoryInterface     $responseFactory
     * @param \Psr\Http\Message\StreamFactoryInterface       $streamFactory
     * @param \BetaKiller\Dev\DebugBarFactoryInterface       $debugBarFactory
     */
    public function __construct(
        private DebugBarAccessControlInterface $accessControl,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private DebugBarFactoryInterface $debugBarFactory
    ) {
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
        if (!$this->accessControl->isAllowedFor($request)) {
            // Forward call
            return $handler->handle($request);
        }

        $ps = RequestProfiler::begin($request, 'Debug middleware (start up)');

        // Fresh instance for every request
        $debugBar = $this->debugBarFactory->create($request, self::BASE_URL);

        RequestProfiler::end($ps);

        $uriPath = $request->getUri()->getPath();

        // Process OpenHandler
        if ($uriPath === self::BASE_URL) {
            $openHandler = new OpenHandler($debugBar);
            $openJson    = $openHandler->handle($request->getQueryParams(), false);

            return $debugBar->getHttpDriver()->applyHeaders(new TextResponse($openJson));
        }

        $isAjax   = ServerRequestHelper::isAjax($request);
        $isStatic = str_starts_with($uriPath, self::BASE_URL);

        $renderer = $debugBar->getJavascriptRenderer();

        $middleware = new PhpDebugBarMiddleware($renderer, $this->responseFactory, $this->streamFactory);

        // Inject DebugBar instance
        $request = DebugServerRequestHelper::withDebugBar($request, $debugBar);

        // Forward call
        $response = match (true) {
            $isAjax => $handler->handle($request),
            default => $middleware->process($request, $handler),
        };

        $isRedirect = ResponseHelper::isRedirect($response);

        if ($isRedirect) {
            // Keep data for next non-redirect call
            $debugBar->stackData();
        }

        if ($isAjax) {
            // Use storage for ajax calls (skip PhpDebugBar static files)
            $debugBar->sendDataInHeaders(true);
        }

        if (!$isStatic && !$isAjax && !$isRedirect) {
            // DebugBar generates inline tags and images so configuring CSP
            $this->addCspRules($renderer, $request);
        }

        // Add headers injected by DebugBar
        return $debugBar->getHttpDriver()->applyHeaders($response);
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
