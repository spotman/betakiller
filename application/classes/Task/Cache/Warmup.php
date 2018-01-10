<?php

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\Url\UrlContainerInterface;

class Task_Cache_Warmup extends \BetaKiller\Task\AbstractTask
{
    /**
     * @var IFaceModelTree
     */
    private $tree;

    /**
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $ifaceStack;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    public function __construct(
        IFaceModelTree $tree,
        IFaceStack $stack,
        IFaceHelper $ifaceHelper,
        UrlContainerHelper $paramsHelper
    ) {
        $this->tree                = $tree;
        $this->ifaceStack          = $stack;
        $this->ifaceHelper         = $ifaceHelper;
        $this->urlParametersHelper = $paramsHelper;

        parent::__construct();
    }

    protected function _execute(array $params)
    {
        $parameters = $this->urlParametersHelper->createEmpty();

        // Get all ifaces recursively
        $iterator = $this->tree->getRecursivePublicIterator();

        // For each IFace
        foreach ($iterator as $ifaceModel) {
            $this->logger->debug('Found IFace :codename', [':codename' => $ifaceModel->getCodename()]);

            try {
                $this->processIFaceModel($ifaceModel, $parameters);
            } catch (Throwable $e) {
                $this->logger->warning('Exception thrown for :iface with message :text', [
                    ':iface' => $ifaceModel->getCodename(),
                    ':text'  => $e->getMessage(),
                ]);
            }
        }
    }

    protected function processIFaceModel(IFaceModelInterface $ifaceModel, UrlContainerInterface $params): void
    {
        $iface = $this->ifaceHelper->createIFaceFromModel($ifaceModel);

        $urls = $this->ifaceHelper->getPublicAvailableUrls($iface, $params);

        $this->logger->debug('Found :count urls', [':count' => count($urls)]);

        foreach ($urls as $url) {
            $this->logger->debug('Selected url = '.$url);

            // No domain coz HMVC do external requests while domain set
            $path = parse_url($url, PHP_URL_PATH);

            // Make HMVC request and check response status
            $this->makeHttpRequest($path);
        }
    }

    protected function makeHttpRequest($url)
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
