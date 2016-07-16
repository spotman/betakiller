<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\Widget;

class Core_Controller_Widget extends Controller {

    /**
     * @return static
     * @throws Widget\Exception
     */
    protected function get_proxy_object()
    {
        $widget_name = $this->param('widget');

        $object = Widget::factory($widget_name, $this->request(), $this->response());

        if ( ! ($object instanceof Widget) )
            throw new Widget\Exception('Widget controller can not serve objects which are not instance of class Widget');

        return $object;
    }

}
