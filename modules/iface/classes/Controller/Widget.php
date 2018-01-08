<?php

class Controller_Widget extends Controller
{
    /**
     * @Inject
     * @var \BetaKiller\Widget\WidgetFactory
     */
    private $widgetFactory;

    /**
     * @return \BetaKiller\Widget\WidgetInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function get_proxy_object()
    {
        $widgetName = $this->param('widget');

        $instance = $this->widgetFactory->create($widgetName);

        $instance
            ->setRequest($this->request)
            ->setResponse($this->response);

        return $instance;
    }
}
