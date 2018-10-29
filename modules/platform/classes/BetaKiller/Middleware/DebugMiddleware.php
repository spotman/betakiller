<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Log\LoggerInterface;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\RequestDataCollector;
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
     * DebugMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param \Psr\Http\Message\StreamFactoryInterface   $streamFactory
     */
    public function __construct(
        AppEnvInterface $appEnv,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger
    ) {
        $this->responseFactory = $responseFactory;
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
        if (!$this->appEnv->isDebugEnabled()) {
            // Forward call
            return $handler->handle($request);
        }

        // Enable debugging via PhpConsole
        if ($this->isPhpConsoleActive()) {
            $this->initPhpConsole();
        }

        $startTime = $request->getServerParams()['REQUEST_TIME_FLOAT'] ?? null;

        // Fresh instance for every request
        $debugBar = new DebugBar();

        $debugBar
            ->addCollector(new TimeDataCollector($startTime))
            ->addCollector(new RequestDataCollector())
            ->addCollector(new MemoryCollector());

        // Storage for processing data for AJAX calls and redirects
        $debugBar->setStorage(new FileStorage($this->appEnv->getTempPath()));

        // Prepare renderer
        $renderer = $debugBar->getJavascriptRenderer('/phpDebugBar');
        $renderer->setEnableJqueryNoConflict(true);
        $middleware = new PhpDebugBarMiddleware($renderer, $this->responseFactory, $this->streamFactory);

        // Forward call
        return $middleware->process($request->withAttribute(DebugBar::class, $debugBar), $handler);
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
