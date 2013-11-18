<?php defined('SYSPATH') OR die('No direct script access.');


class Kohana_Widget {

    const DEFAULT_STATE = 'index';

    /**
     * @var string
     */
    protected $name;

    protected $current_state = self::DEFAULT_STATE;

    /**
     * @param $name
     * @return Widget
     */
    public static function factory($name)
    {
        $class_name = static::get_class_prefix() . $name;

        return new $class_name($name);
    }

    function __construct($name)
    {
        $this->name = $name;
    }

    public function render()
    {
        $view = $this->get_view();
        return $view;
    }

    public function __toString()
    {
        return (string) $this->render();
    }

    protected function & request()
    {
        return Request::current();
    }

    protected function & response()
    {
        return Response::current();
    }

    protected function param($key = NULL, $default = NULL)
    {
        return $this->request()->param($key, $default);
    }

    protected function get_view($state = NULL)
    {
        return $this->get_state_view($state ?: $this->current_state);
    }

    private function get_state_view($state)
    {
        $view_path = 'widget'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->name) . DIRECTORY_SEPARATOR . $state;

        return $this->view_factory($view_path);
    }

    private function view_factory($path)
    {
        return View::factory($path);
    }

    private static function get_class_prefix()
    {
        return 'Widget_';
    }

}