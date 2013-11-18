<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Controller_Widget extends Controller_Proxy {

    /**
     * @return static
     * @throws Kohana_Exception
     */
    protected function get_proxy_object()
    {
        $widget_name = $this->request->param('widget');

        $object = Widget::factory($widget_name);

        if ( ! ($object instanceof Widget) )
            throw new Kohana_Exception('Widget controller can not serve objects which are not instance of class Widget');

        return $object;
    }

}