<?php

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\IFace\Url\UrlParameters;
use BetaKiller\IFace\Url\UrlParametersInterface;

class Task_Cache_Warmup extends Minion_Task
{
    use BetaKiller\Helper\IFaceTrait;

    /**
     * @var IFaceModelTree
     */
    private $tree;

    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

    public function __construct(IFaceModelTree $tree, IFaceProvider $provider)
    {
        $this->tree          = $tree;
        $this->ifaceProvider = $provider;

        parent::__construct();
    }

    protected function _execute(array $params)
    {
        $tree = $this->tree;

        $parameters = UrlParameters::create();

        // Get all ifaces recursively
        $iterator = $tree->getRecursivePublicIterator();

        // For each IFace
        foreach ($iterator as $ifaceModel) {
            $this->debug('Found IFace :codename', [':codename' => $ifaceModel->getCodename()]);

            try {
                $this->processIFaceModel($ifaceModel, $parameters);
            } catch (Exception $e) {
                $this->warning('Exception thrown for :iface with message :text', [
                    ':iface'    => $ifaceModel->getCodename(),
                    ':text'     => $e->getMessage(),
                ]);
            }
        }
    }

    protected function processIFaceModel(IFaceModelInterface $ifaceModel, UrlParametersInterface $params)
    {
        $iface = $this->ifaceProvider->fromModel($ifaceModel);

        $urls = $iface->getAvailableUrls($params, 1, false); // No domain coz HMVC do external requests while domain set
        $this->debug(PHP_EOL.implode(PHP_EOL, $urls).PHP_EOL);

        $url = array_pop($urls);

        // Make HMVC request and check response status
        $this->make_http_request($url);
    }

    protected function make_http_request($url)
    {
        $this->debug('Making request to :url', [':url' => $url]);

        // Reset parameters between internal requests
        $this->url_dispatcher()->reset();

        $request = new Request($url);
        $response = $request->execute();
        $status = $response->status();

        if ($status === 200)
        {
            // TODO Maybe grab page content, parse it and make request to every image/css/js file

            $this->info('Cache was warmed up for :url', [':url' => $url]);
        }
        elseif ($status < 400)
        {
            $this->info('Redirect :status received for :url', [':url' => $url, ':status' => $status]);
        }
        elseif (in_array($status, [401, 403], true))
        {
            $this->info('Access denied with :status status for :url', [':url' => $url, ':status' => $status]);
        }
        else
        {
            $this->warning('Got :status status for URL :url', [':url' => $url, ':status' => $status]);
        }
    }
}
