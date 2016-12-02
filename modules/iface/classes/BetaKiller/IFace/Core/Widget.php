<?php
namespace BetaKiller\IFace\Core;

use BetaKiller\IFace\WidgetFactory;
use Controller;
use Kohana_Exception;
use Request;
use Response;
use Route;
use Validation;

use BetaKiller\IFace\Widget\Exception;

abstract class Widget extends \Controller // TODO Remove extension and replace it via Helper\Controller
{
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

    public function __construct($name, Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $this->setName($name);

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
     * @deprecated
     */
    public static function factory($name, Request $request = NULL, Response $response = NULL)
    {
        return WidgetFactory::instance()->create($name, $request, $response);
    }

    /**
     * Setter for widget name
     *
     * @param string $value
     * @return $this
     */
    public function setName($value)
    {
        $this->_name = $value;

        return $this;
    }

    /**
     * Getter for widget name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Setter for widget context (additional data for rendering)
     *
     * @param array $value
     * @return $this
     */
    public function setContext(array $value)
    {
        $this->_context = $value;

        return $this;
    }

    /**
     * Getter for widget context (additional data for rendering)
     *
     * @return array
     */
    public function getContext()
    {
        return $this->_context;
    }

    public function getContextParam($name, $default = null)
    {
        return isset($this->_context[$name]) ? $this->_context[$name] : $default;
    }

    /**
     * Renders widget and returns its representation
     * @return string
     */
    public function __toString()
    {
        try {
            $response = $this->render();
        } catch (\Exception $e) {
            $response = Kohana_Exception::_handler($e);
        }

        return (string)$response;
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

        // TODO reset data and context for next render
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

        // Serve widget name
        $data['name'] = $this->getName();

        // Creating View instance
        $view = $this->view();

        // Assigning data (override context keys)
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
        return [];
    }

    /**
     * @param string $current_state
     */
    public function set_current_state($current_state)
    {
        $this->_current_state = $current_state;
    }

    /**
     * @return string
     */
    public function get_current_state()
    {
        return $this->_current_state;
    }

    protected function url($action = NULL, $protocol = TRUE)
    {
        return Route::url('widget-controller', array('widget' => $this->getName(), 'action' => $action), $protocol);
    }

    protected function view($file = NULL)
    {
        if (!$file) {
            $suffix = $this->_current_state != static::DEFAULT_STATE
                ? '-' . $this->_current_state
                : '';

            $file = str_replace('_', DIRECTORY_SEPARATOR, $this->_name) . $suffix;
        }

        $view_path = 'widgets' . DIRECTORY_SEPARATOR . $file;

        return $this->view_factory($view_path, $this->getContext());
    }

    protected function _execute()
    {
        throw new Exception('Direct call is not allowed');
    }

    protected function get_validation_errors(Validation $validation)
    {
        return $validation->errors($this->get_validation_messages_path());
    }

    private function get_validation_messages_path()
    {
        return 'widgets' . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->_name);
    }

    /**
     * @param string $message
     * @param array $variables
     * @throws \BetaKiller\IFace\Widget\Exception
     */
    protected function throw_exception($message, array $variables = [])
    {
        throw new Exception($message, $variables);
    }
}
