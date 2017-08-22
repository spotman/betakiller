<?php

use BetaKiller\IFace\WidgetFactory;

class Controller_Widget extends Controller
{
    /**
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     * @throws \BetaKiller\IFace\Widget\WidgetException
     */
    protected function get_proxy_object()
    {
        $widget_name = $this->param('widget');

        return WidgetFactory::getInstance()->create($widget_name, $this->getRequest(), $this->getResponse());
    }
}
