<?php defined('SYSPATH') OR die('No direct script access.');


abstract class Kohana_Widget extends Controller {

    const DEFAULT_STATE = 'default';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string Current widget state (for Finite State Machine)
     */
    protected $current_state = self::DEFAULT_STATE;

    /**
     * @param $name Widget name
     * @param Request $request
     * @param Response $response
     * @return static
     */
    public static function factory($name, Request $request = NULL, Response $response = NULL)
    {
        $class_name = static::get_class_prefix() . $name;

        // Getting current request if none provided
        $request = $request ?: Request::current();

        // Creating empty response if none provided
        $response = $response ?: Response::factory();

        /** @var Widget $widget */
        $widget = new $class_name($request, $response);

        $widget->name($name);

        return $widget;
    }

    /**
     * Getter/setter for widget name
     * @param string|null $value
     * @return $this|string
     */
    public function name($value = NULL)
    {
        if ( $value === NULL )
            return $this->name;

        $this->name = $value;
        return $this;
    }

    /**
     * Renders widget and returns its representation
     * @return string
     */
    public function __toString()
    {
        try
        {
            $response = $this->render();
        }
        catch ( Exception $e )
        {
            $response = Kohana_Exception::_handler($e);
        }

        return (string) $response;
    }

    public function action_render()
    {
        $this->render();
    }

    public function render()
    {
        // TODO call_user_func_array
        $this->_render();
        return $this->response();
    }

    /**
     * Generates HTML/CSS/JS representation of the widget
     * Implement this method in your widget
     * Use $this->send_string() / $this->send_json() / $this->send_jsonp() methods to populate output
     */
    abstract protected function _render();

    protected function url($action = NULL, $protocol = TRUE)
    {
        $widget = str_replace($this->get_class_prefix(), '', get_class($this));

        return Route::url('widget-controller', array('widget' => $widget, 'action' => $action), $protocol);
    }

    protected function view($state = NULL)
    {
        return $this->state_view($state ?: $this->current_state);
    }

    private function state_view($state)
    {
        $view_path = 'widgets'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->name) . DIRECTORY_SEPARATOR . $state;

        return $this->view_factory($view_path);
    }

    protected static function get_class_prefix()
    {
        return 'Widget_';
    }

}