<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\IFace\WidgetFactory;
use BetaKiller\Utils\Kohana\ControllerHelperTrait;
use BetaKiller\Utils\Kohana\Request;
use BetaKiller\Utils\Kohana\Response;
use Route;
use Validation;
use View;

abstract class AbstractWidget implements WidgetInterface
{
    use ControllerHelperTrait;

    const DEFAULT_STATE = 'default';

    const JSON_SUCCESS = Response::JSON_SUCCESS;
    const JSON_ERROR   = Response::JSON_ERROR;

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
    protected $_context = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param null       $file
     * @param array|NULL $data
     *
     * @return View
     */
    abstract protected function view_factory($file = null, array $data = null);

    /**
     * @param string   $name Widget name
     * @param Request  $request
     * @param Response $response
     *
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     * @deprecated Use WidgetFactory instead
     */
    public static function factory($name, Request $request = null, Response $response = null)
    {
        return WidgetFactory::getInstance()->create($name, $request, $response);
    }

    /**
     * Widget constructor.
     * Empty by default, use WidgetFactory for creating Widget instances
     */
    public function __construct()
    {
    }

    /**
     * Setter for widget name
     *
     * @param string $value
     *
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
     *
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
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Throwable $e) {
            return $this->processException($e);
        }
    }

    /**
     * @param \Exception|\\Throwable $e
     *
     * @return string
     * @todo Rewrite to ExceptionHandler and move exception handling logic to it
     */
    protected function processException($e)
    {
        try {
            return (string)WidgetException::_handler($e);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Default action for Controller_Widget
     * @deprecated Rewrite widget controller to direct Widget::render() call
     */
    public function action_render()
    {
        $this->_render();
    }

    /**
     * Renders widget View
     *
     * @return string
     */
    public function render()
    {
        $this->_render();

        // TODO reset data and context for next render
        return (string)$this->getResponse();
    }

    /**
     * Generates HTML/CSS/JS representation of the widget
     * Use $this->send_string() / $this->send_json() / $this->send_jsonp() methods to populate output
     * Override this method in your widget if default behaviour is not enough for you
     */
    protected function _render()
    {
        // Collecting data
        $data = $this->getData();

        // Serve widget data
        $data['this'] = [
            'name' => $this->getName(),
        ];

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
    public function getData()
    {
        return [];
    }

    /**
     * @param string $current_state
     */
    public function setCurrentState($current_state)
    {
        $this->_current_state = $current_state;
    }

    /**
     * @return string
     */
    public function getCurrentState()
    {
        return $this->_current_state;
    }

    protected function url($action = null, $protocol = true)
    {
        return Route::url('widget-controller', ['widget' => $this->getName(), 'action' => $action], $protocol);
    }

    /**
     * @param string|null $file
     *
     * @return \View
     */
    protected function view($file = null)
    {
        if (!$file) {
            $suffix = $this->_current_state !== static::DEFAULT_STATE
                ? '-'.$this->_current_state
                : '';

            $file = str_replace('_', DIRECTORY_SEPARATOR, $this->_name).$suffix;
        }

        $view_path = 'widgets'.DIRECTORY_SEPARATOR.$file;

        return $this->view_factory($view_path, $this->getContext());
    }

    protected function get_validation_errors(Validation $validation)
    {
        return $validation->errors($this->get_validation_messages_path());
    }

    private function get_validation_messages_path()
    {
        return 'widgets'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $this->_name);
    }

    /**
     * @param string $message
     * @param array  $variables
     *
     * @throws \BetaKiller\IFace\Widget\WidgetException
     */
    protected function throw_exception($message, array $variables = [])
    {
        throw new WidgetException($message, $variables);
    }
}
