<?php
declare(strict_types=1);

namespace BetaKiller\Task\Cache;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Service\HttpClientService;
use BetaKiller\Task\TaskException;
use BetaKiller\Url\AvailableUrlsCollector;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class Warmup extends \BetaKiller\Task\AbstractTask
{
    /**
     * @var \BetaKiller\Url\AvailableUrlsCollector
     */
    private $urlCollector;

    /**
     * @var \BetaKiller\Factory\UrlHelperFactory
     */
    private $urlHelperFactory;

    /**
     * @var \BetaKiller\Service\HttpClientService
     */
    private $httpClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $serverHost = '127.0.0.1';

    private $serverPort = 8099;

    /**
     * @var \Symfony\Component\Process\Process
     */
    private $serverProcess;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * Warmup constructor.
     *
     * @param \BetaKiller\Url\AvailableUrlsCollector $urlCollector
     * @param \BetaKiller\Factory\UrlHelperFactory   $urlHelperFactory
     * @param \BetaKiller\Service\HttpClientService  $httpClient
     * @param \Psr\Log\LoggerInterface               $logger
     */
    public function __construct(
        AvailableUrlsCollector $urlCollector,
        UrlHelperFactory $urlHelperFactory,
        HttpClientService $httpClient,
        AppEnvInterface $appEnv,
        LoggerInterface $logger
    ) {
        $this->urlCollector     = $urlCollector;
        $this->urlHelperFactory = $urlHelperFactory;
        $this->httpClient       = $httpClient;
        $this->appEnv           = $appEnv;
        $this->logger           = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $urlHelper = $this->urlHelperFactory->create();

        $items   = $this->urlCollector->getPublicAvailableUrls($urlHelper);
        $counter = 0;

        if ($this->canConnectToServer()) {
            throw new TaskException('Something is already running on :host::port', [
                ':host' => $this->serverHost,
                ':port' => $this->serverPort,
            ]);
        }

        // Start internal php web-server
        $this->startServer();

        $start     = microtime(true);
        $connected = false;
        $timeout   = 5;

        // Try to connect until the time spent exceeds the timeout specified in the configuration
        while (microtime(true) - $start <= $timeout) {
            if ($this->canConnectToServer()) {
                $connected = true;
                break;
            }
        }

        if (!$connected) {
            $this->stopServer();

            throw new TaskException('Web server connection timeout :sec second(s)', [
                ':sec' => $timeout,
            ]);
        }

        // Make HTTP requests to temporary created PHP internal web-server instance
        foreach ($items as $item) {
            $url = $item->getUrl();
            $this->logger->debug('Selected url = '.$url);

            // Make HMVC request and check response status
            $this->makeHttpRequest($url);

            $counter++;
        }

        $this->stopServer();

        $this->logger->info(':count URLs processed', [':count' => $counter]);
    }

    private function startServer(): void
    {
        $docRoot = $this->appEnv->getDocRootPath();

        $command = sprintf('%s -S %s:%d -t %s %s', // >/dev/null 2>&1
            PHP_BINARY,
            $this->serverHost,
            $this->serverPort,
            $docRoot,
            $docRoot.DIRECTORY_SEPARATOR.'index.php'
        );

        $process = new Process($command, $docRoot, [
            AppEnvInterface::APP_MODE => $this->appEnv->getModeName(),
        ]);

        $process
            ->disableOutput()
            ->setTimeout(null)
            ->setIdleTimeout(null)
            ->setTty(false);

        $process->start();

        if (!$process->isRunning()) {
            throw new \RuntimeException('Could not start the web server');
        }

        $this->serverProcess = $process;
    }

    private function stopServer(): void
    {
        $this->serverProcess->stop(5);
    }

    /**
     * See if we can connect to the internal php server
     *
     * @return boolean
     */
    private function canConnectToServer(): bool
    {
        // Disable error handler for now
        set_error_handler(function () {
            return true;
        });

        // Try to open a connection
        $sp = fsockopen($this->serverHost, $this->serverPort);

        // Restore the handler
        restore_error_handler();

        if ($sp === false) {
            return false;
        }

        fclose($sp);

        return true;
    }

    private function makeHttpRequest(string $url): void
    {
        $this->logger->debug('Making request to :url', [':url' => $url]);

        // see https://github.com/guzzle/guzzle/issues/590
        try {
            $request  = $this->httpClient->get($url);
            $response = $this->httpClient->syncCall($request, [
                'curl' => [
                    CURLOPT_INTERFACE => $this->serverHost,
                    CURLOPT_PORT      => $this->serverPort,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('Got exception :e for url :url', [':url' => $url, ':e' => $e->getMessage()]);

            return;
        }

        $status = $response->getStatusCode();

        if ($status === 200) {
            // TODO Maybe grab page content, parse it and make request to every image/css/js file

            $this->logger->info('Cache was warmed up for :url', [':url' => $url]);
        } elseif ($status >= 300 && $status < 400) {
            $this->logger->info('Redirect :status received for :url', [
                ':url'    => $url,
                ':status' => $status,
            ]);
        } elseif (\in_array($status, [401, 403], true)) {
            $this->logger->info('Access denied with :status status for :url', [':url' => $url, ':status' => $status]);
        } else {
            $this->logger->warning('Got :status status for URL :url', [':url' => $url, ':status' => $status]);
        }
    }
}
