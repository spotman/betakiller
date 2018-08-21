<?php
declare(strict_types=1);

namespace BetaKiller\Task\Cache;

use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\Url\AvailableUrlsCollector;
use BetaKiller\Url\UrlElementStack;
use Psr\Log\LoggerInterface;
use Request;
use Throwable;

class Warmup extends \BetaKiller\Task\AbstractTask
{
    /**
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\Url\UrlElementStack
     */
    private $ifaceStack;

    /**
     * @var \BetaKiller\Url\AvailableUrlsCollector
     */
    private $urlCollector;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        AvailableUrlsCollector $urlCollector,
        UrlElementStack $stack,
        UrlContainerHelper $paramsHelper,
        LoggerInterface $logger
    ) {
        $this->ifaceStack          = $stack;
        $this->urlCollector        = $urlCollector;
        $this->urlParametersHelper = $paramsHelper;
        $this->logger              = $logger;

        parent::__construct();
    }

    public function run(): void
    {
        $items   = $this->urlCollector->getPublicAvailableUrls();
        $counter = 0;

        foreach ($items as $item) {
            $url = $item->getUrl();
            $this->logger->debug('Selected url = '.$url);

            // No domain coz HMVC do external requests while domain set
            $path = parse_url($url, PHP_URL_PATH);

            // Make HMVC request and check response status
            $this->makeHttpRequest($path);

            $counter++;
        }

        $this->logger->info(':count URLs processed', [':count' => $counter]);
    }

    private function makeHttpRequest(string $url): void
    {
        $this->logger->debug('Making request to :url', [':url' => $url]);

        // Reset parameters between internal requests
        // TODO remove this trick
        $this->urlParametersHelper->getCurrentUrlParameters()->clear();
        $this->ifaceStack->clear();

        try {
            $request  = new Request($url, [], false);
            $response = $request->execute();
            $status   = $response->status();
        } catch (Throwable $e) {
            $this->logger->warning('Got exception :e for url :url', [':url' => $url, ':e' => $e->getMessage()]);

            return;
        }

        if ($status === 200) {
            // TODO Maybe grab page content, parse it and make request to every image/css/js file

            $this->logger->info('Cache was warmed up for :url', [':url' => $url]);
        } elseif ($status >= 300 && $status < 400) {
            $this->logger->info('Redirect :status received for :url', [
                ':url'    => $url,
                ':status' => $status,
            ]);
            $this->logger->debug('Headers are :values', [':values' => json_encode($response->headers())]);
        } elseif (in_array($status, [401, 403], true)) {
            $this->logger->info('Access denied with :status status for :url', [':url' => $url, ':status' => $status]);
        } else {
            $this->logger->warning('Got :status status for URL :url', [':url' => $url, ':status' => $status]);
        }
    }
}
