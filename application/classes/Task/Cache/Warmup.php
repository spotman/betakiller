<?php
use BetaKiller\IFace\IFaceModelTree;

class Task_Cache_Warmup extends Minion_Task
{
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
        /** @var \BetaKiller\IFace\IFaceModelTree $tree */
        $tree = \BetaKiller\DI\Container::instance()->get(\BetaKiller\IFace\IFaceModelTree::class);

        $params = $this->url_parameters_instance();

        // Get all ifaces recursively
        $iterator = $tree->getRecursivePublicIterator();

        // For each IFace
        foreach ($iterator as $iface_model)
        {
            $this->debug('Found IFace :codename', [':codename' => $iface_model->get_codename()]);


            $urls = $this->_dispatcher->get_iface_model_available_urls($iface_model, $params, 1);
            $this->debug(implode(PHP_EOL, $urls).PHP_EOL);

            $url = array_pop($urls);

            // Make HMVC request and check response status
            $this->make_http_request($url);
        }
    }

    protected function make_http_request($url)
    {
        $response = Request::factory($url)->execute();
        $status = $response->status();

        if ($status == 200)
        {
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
