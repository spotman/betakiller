<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Log\Logger;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\Storage\FileStorage;
use PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Log\Logger
     */
    private $logger;

    /**
     * DebugMiddleware constructor.
     *
     * @param \BetaKiller\Log\Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
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
            ->addCollector(new MessagesCollector())
            ->addCollector(new RequestDataCollector())
            ->addCollector(new TimeDataCollector($startTime))
            ->addCollector(new MemoryCollector())
            ->addCollector(new MonologCollector($this->logger->getMonolog()));

        // Storage for processing data for AJAX calls and redirects
        $debugBar->setStorage(new FileStorage(\sys_get_temp_dir()));

        // Initialize profiler with DebugBar instance and enable it
        $profiler = ServerRequestHelper::getProfiler($request);
        $profiler->enable($debugBar);

        // Prepare renderer
        $renderer   = $debugBar->getJavascriptRenderer('/phpDebugBar');
        $middleware = new PhpDebugBarMiddleware($renderer);

        // Forward call
        return $middleware->process($request, $handler);
    }
}
