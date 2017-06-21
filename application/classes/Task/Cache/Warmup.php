<?php

use BetaKiller\Helper\UrlParametersHelper;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\IFace\Url\UrlParametersInterface;

class Task_Cache_Warmup extends Minion_Task
{
    /**
     * @var IFaceModelTree
     */
    private $tree;

    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

    /**
     * @var \BetaKiller\Helper\UrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $ifaceStack;

    public function __construct(
        IFaceModelTree $tree,
        IFaceStack $stack,
        IFaceProvider $provider,
        UrlParametersHelper $paramsHelper
    ) {
        $this->tree                = $tree;
        $this->ifaceStack          = $stack;
        $this->ifaceProvider       = $provider;
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
            $this->debug('Found IFace :codename', [':codename' => $ifaceModel->getCodename()]);

            try {
                $this->processIFaceModel($ifaceModel, $parameters);
            } catch (Exception $e) {
                $this->warning('Exception thrown for :iface with message :text', [
                    ':iface' => $ifaceModel->getCodename(),
                    ':text'  => $e->getMessage(),
                ]);
            }
        }
    }

    protected function processIFaceModel(IFaceModelInterface $ifaceModel, UrlParametersInterface $params)
    {
        $iface = $this->ifaceProvider->fromModel($ifaceModel);

        $urls = $iface->getAvailableUrls($params, 1);
        $url  = array_pop($urls);
        $this->debug('Selected url = '.$url);

        // No domain coz HMVC do external requests while domain set
        $path = parse_url($url, PHP_URL_PATH);

        // Make HMVC request and check response status
        $this->makeHttpRequest($path);
    }

    protected function makeHttpRequest($url)
    {
        $this->debug('Making request to :url', [':url' => $url]);

        // Reset parameters between internal requests
        // TODO remove this trick
        $this->urlParametersHelper->getCurrentUrlParameters()->clear();
        $this->ifaceStack->clear();

        $request  = new Request($url, [], false);
        $response = $request->execute();
        $status   = $response->status();

        if ($status === 200) {
            // TODO Maybe grab page content, parse it and make request to every image/css/js file

            $this->info('Cache was warmed up for :url', [':url' => $url]);
        } elseif ($status >= 300 && $status < 400) {
            $this->info('Redirect :status received for :url', [
                ':url'    => $url,
                ':status' => $status,
            ]);
            $this->debug('Headers are :values', [
                ':values' => print_r($response->headers(), true),
            ]);
        } elseif (in_array($status, [401, 403], true)) {
            $this->info('Access denied with :status status for :url', [':url' => $url, ':status' => $status]);
        } else {
            $this->warning('Got :status status for URL :url', [':url' => $url, ':status' => $status]);
        }
    }
}
