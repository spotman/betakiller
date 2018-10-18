<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Log\Logger;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\RequestIdGenerator;
use DebugBar\Storage\FileStorage;
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
     * @var \BetaKiller\Log\Logger
     */
    private $logger;

    /**
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * DebugMiddleware constructor.
     *
     * @param \BetaKiller\Log\Logger                     $logger
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param \Psr\Http\Message\StreamFactoryInterface   $streamFactory
     */
    public function __construct(
        Logger $logger,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->logger          = $logger;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
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
        $startTime = $request->getServerParams()['REQUEST_TIME_FLOAT'] ?? null;

        // Fresh instance for every request
        $debugBar = new DebugBar();

        $debugBar
            ->addCollector(new RequestDataCollector())
            ->addCollector(new TimeDataCollector($startTime))
            ->addCollector(new MonologCollector($this->logger->getMonolog()))
            ->addCollector(new MemoryCollector());

        // Storage for processing data for AJAX calls and redirects
        $debugBar->setStorage(new FileStorage(\sys_get_temp_dir()));

        // Initialize profiler with DebugBar instance and enable it
        $profiler = ServerRequestHelper::getProfiler($request);
        $profiler->enable($debugBar);

        // Prepare renderer
        $renderer = $debugBar->getJavascriptRenderer('/phpDebugBar');
        $renderer->setEnableJqueryNoConflict(true);
        $middleware = new PhpDebugBarMiddleware($renderer, $this->responseFactory, $this->streamFactory);

        // Forward call
        return $middleware->process($request->withAttribute(DebugBar::class, $debugBar), $handler);
    }
}
