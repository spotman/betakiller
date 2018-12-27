<?php
declare(strict_types=1);

namespace BetaKiller\Task\Cache;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Service\HttpClientService;
use BetaKiller\Task\TaskException;
use BetaKiller\Url\AvailableUrlsCollector;
use Psr\Http\Message\ResponseInterface;
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

    /**
     * @var string
     */
    private $serverHost;

    /**
     * @var int
     */
    private $serverPort;

    private $timeout = 5;

    /**
     * @var \Symfony\Component\Process\Process
     */
    private $serverProcess;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * Warmup constructor.
     *
     * @param \BetaKiller\Url\AvailableUrlsCollector $urlCollector
     * @param \BetaKiller\Factory\UrlHelperFactory   $urlHelperFactory
     * @param \BetaKiller\Service\HttpClientService  $httpClient
     * @param \BetaKiller\Helper\AppEnvInterface     $appEnv
     * @param \BetaKiller\Config\AppConfigInterface  $appConfig
     * @param \Psr\Log\LoggerInterface               $logger
     */
    public function __construct(
        AvailableUrlsCollector $urlCollector,
        UrlHelperFactory $urlHelperFactory,
        HttpClientService $httpClient,
        AppEnvInterface $appEnv,
        AppConfigInterface $appConfig,
        LoggerInterface $logger
    ) {
        $this->urlCollector     = $urlCollector;
        $this->urlHelperFactory = $urlHelperFactory;
        $this->httpClient       = $httpClient;
        $this->appEnv           = $appEnv;
        $this->appConfig        = $appConfig;
        $this->logger           = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->serverHost = (string)\getenv('WARMUP_HOST');
        $this->serverPort = (int)\getenv('WARMUP_PORT');

        if (!$this->serverHost || !$this->serverPort) {
            throw new TaskException('Host and port must be defined via env vars');
        }

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

        // Try to connect until the time spent exceeds the timeout specified in the configuration
        while (microtime(true) - $start <= $this->timeout) {
            if ($this->canConnectToServer()) {
                $connected = true;
                break;
            }
            \usleep(100000);
        }

        if (!$connected) {
            $this->stopServer();

            throw new TaskException('Web server connection timeout :sec second(s)', [
                ':sec' => $this->timeout,
            ]);
        }

        $urlHelper = $this->urlHelperFactory->create();
        $items     = $this->urlCollector->getPublicAvailableUrls($urlHelper);
        $counter   = 0;

        // Make HTTP requests to temporary created PHP internal web-server instance
        foreach ($items as $item) {
            $url = $item->getUrl();

            // Make HMVC request and check response status
            $this->warmupUrl($url);

            $counter++;
        }

        $this->logger->info(':count dynamic URLs processed', [':count' => $counter]);

        $this->checkRequiredFiles();
        $this->checkAuthRequired();

        $this->stopServer();
    }

    private function startServer(): void
    {
        $this->logger->debug('Starting internal web-server');

        $docRoot = $this->appEnv->getDocRootPath();

        $command = [
//            'exec',
            PHP_BINARY,
            '-S',
            $this->serverHost.':'.$this->serverPort,
            '-t',
            $docRoot,
            $docRoot.DIRECTORY_SEPARATOR.'index.php',
            // >/dev/null 2>&1
        ];

        $this->logger->debug(implode(' ', $command));

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

        $this->logger->debug('Web-server started at :host::port with PID = :pid', [
            ':host' => $this->serverHost,
            ':port' => $this->serverPort,
            ':pid'  => $process->getPid(),
        ]);

        $this->serverProcess = $process;
    }

    private function stopServer(): void
    {
        $this->logger->debug('Shutting down internal web-server');

        $this->serverProcess->stop(5);

        $start   = microtime(true);
        $stopped = false;

        // Try to connect until the time spent exceeds the timeout specified in the configuration
        while (microtime(true) - $start <= $this->timeout) {
            if ($this->serverProcess->isTerminated()) {
                $stopped = true;
                break;
            }
        }

        if (!$stopped) {
            throw new TaskException('Could not stop the web server, kill it by PID = :pid', [
                ':pid' => $this->serverProcess->getPid(),
            ]);
        }
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

    private function warmupUrl(string $url): void
    {
        $response = $this->makeHttpRequest($url);

        $status = $response->getStatusCode();

        if ($status === 200) {
            if ($response->getBody()->getSize() > 0) {
                // TODO Maybe grab page content, parse it and make request to every image/css/js file
                $this->logger->info('Cache was warmed up for :url', [':url' => $url]);
            } else {
                $this->logger->warning('Got :status status with empty content for URL :url', [
                    ':url'    => $url,
                    ':status' => $status,
                ]);
            }
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

    private function checkRequiredFiles(): void
    {
        $files = [
            '/sitemap.xml',
            '/robots.txt',
            '/favicon.ico',
        ];

        foreach ($files as $file) {
            $url      = (string)$this->appConfig->getBaseUri()->withPath($file);
            $response = $this->makeHttpRequest($url);

            if ($response->getStatusCode() !== 200) {
                throw new TaskException('Missing required file :file', [
                    ':file' => $file,
                ]);
            }
        }
    }

    private function checkAuthRequired(): void
    {
        $urls = [
            '/admin',
        ];

        foreach ($urls as $url) {
            $url      = (string)$this->appConfig->getBaseUri()->withPath($url);
            $response = $this->makeHttpRequest($url);

            if ($response->getStatusCode() !== 401) {
                throw new TaskException('Auth must be required in :url', [
                    ':url' => $url,
                ]);
            }
        }
    }

    private function makeHttpRequest(string $url, array $options = null): ResponseInterface
    {
        $path = \parse_url($url, \PHP_URL_PATH);

        // Internal PHP web-server can not handle SSL
        $url = 'http://'.$this->serverHost.':'.$this->serverPort.$path;

        $this->logger->debug('Making request to :url', [':url' => $url]);

        // see https://github.com/guzzle/guzzle/issues/590
        $request = $this->httpClient->get($url);

        return $this->httpClient->syncCall($request, $options);
    }
}
