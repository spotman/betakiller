<?php
use BetaKiller\IFace\IFaceModelTree;

class Task_Cache_Warmup extends Minion_Task
{
    use BetaKiller\Helper\IFaceTrait;

    /**
     * @var IFaceModelTree
     */
    protected $_tree;

    /**
     * @var \URL_Dispatcher
     */
    protected $_dispatcher;

    public function __construct(IFaceModelTree $tree, \URL_Dispatcher $dispatcher)
    {
        $this->_tree = $tree;
        $this->_dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function _execute(array $params)
    {
        $tree = $this->_tree;

        $parameters = $this->url_parameters_instance();

        // Get all ifaces recursively
        $iterator = $tree->getRecursivePublicIterator();

        // For each IFace
        foreach ($iterator as $iface_model)
        {
            $this->debug('Found IFace :codename', [':codename' => $iface_model->get_codename()]);

            try {
                $this->process_iface($iface_model, $parameters);
            } catch (Exception $e) {
                $this->warning('Exception thrown for :iface with message :text', [
                    ':iface'    => $iface_model->get_codename(),
                    ':text'     => $e->getMessage(),
                ]);
            }
        }
    }

    protected function process_iface(\BetaKiller\IFace\IFaceModelInterface $iface_model, URL_Parameters $params)
    {
        $urls = $this->_dispatcher->get_iface_model_available_urls($iface_model, $params, 1, FALSE);
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

        if ($status == 200)
        {
            // TODO Maybe grab page content, parse it and make request to every image/css/js file

            $this->info('Cache was warmed up for :url', [':url' => $url]);
        }
        elseif ($status < 400)
        {
            $this->info('Redirect :status received for :url', [':url' => $url, ':status' => $status]);
        }
        elseif (in_array($status, [401, 403]))
        {
            $this->info('Access denied with :status status for :url', [':url' => $url, ':status' => $status]);
        }
        else
        {
            $this->warning('Got :status status for URL :url', [':url' => $url, ':status' => $status]);
        }
    }
}
