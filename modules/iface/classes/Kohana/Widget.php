<?php defined('SYSPATH') OR die('No direct script access.');


abstract class Kohana_Widget extends Controller {

    use Util_GetterAndSetterMethod;

    const DEFAULT_STATE = 'default';

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string Current widget state (for Finite State Machine)
     */
    protected $_current_state = self::DEFAULT_STATE;

    /**
     * @var array Context for widget rendering
     */
    protected $_context = array();

    /**
     * @var array Additional data for rendering
     */
    protected $_data = array();

    public function __construct($name, Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $this->name($name);

        $this->_init();
    }

    /**
     * Custom widget initialization
     * You may set $_current_state here
     */
    protected function _init()
    {
        // Empty by default
    }

    /**
     * @param string $name Widget name
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
        return new $class_name($name, $request, $response);
    }

    /**
     * Getter/setter for widget name
     *
     * @param string|null $value
     * @return $this|string
     */
    public function name($value = NULL)
    {
        return $this->getter_and_setter_method('_name', $value);
    }

    /**
     * Getter/setter for widget context (additional data for rendering)
     * @param array|null $value
     * @return mixed
     */
    public function context(array $value = NULL)
    {
        return $this->getter_and_setter_method('_context', $value);
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

    /**
     * Default action for Controller_Widget
     */
    public function action_render()
    {
        $this->_render();
    }

    /**
     * Renders widget View
     *
     * @return Response
     */
    public function render()
    {
        $this->_render();
        return $this->response();
    }

    /**
     * Generates HTML/CSS/JS representation of the widget
     * Use $this->send_string() / $this->send_json() / $this->send_jsonp() methods to populate output
     * Override this method in your widget if default behaviour is not enough for you
     */
    protected function _render()
    {
        // Collecting data
        $data = $this->get_data();

        // Creating View instance
        $view = $this->view();

        // Assigning data
        $view->set($data);

        // Sending View to output
        $this->send_view($view);
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
    {
        return $this->_data;
    }

    public function set_data($key, $value)
    {
        if ( is_array($key) )
        {
            $this->_data = array_merge($this->_data, $key);
        }
        else
        {
            $this->_data[$key] = $value;
        }
    }

    protected function url($action = NULL, $protocol = TRUE)
    {
        $widget = str_replace($this->get_class_prefix(), '', get_class($this));

        return Route::url('widget-controller', array('widget' => $widget, 'action' => $action), $protocol);
    }

    protected function view($state = NULL)
    {
        return $this->state_view($state ?: $this->_current_state);
    }

    private function state_view($state)
    {
        $view_path = 'widgets'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->_name) . DIRECTORY_SEPARATOR . $state;

        return $this->view_factory($view_path, $this->_context);
    }

    protected static function get_class_prefix()
    {
        return 'Widget_';
    }

    protected function _execute()
    {
        throw new HTTP_Exception_500('Direct call is not allowed');
    }

}