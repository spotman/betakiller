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

    protected const DEFAULT_STATE = 'default';

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
     * @return \View
     * @deprecated
     * @todo Inject view factory in constructor
     */
    abstract protected function view_factory($file = null, array $data = null): View;

    /**
     * @param string   $name Widget name
     * @param Request  $request
     * @param Response $response
     *
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     * @deprecated Use WidgetFactory instead
     */
    public static function factory($name, Request $request = null, Response $response = null): WidgetInterface
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
    public function setName(string $value)
    {
        $this->_name = $value;

        return $this;
    }

    /**
     * Getter for widget name
     *
     * @return string
     */
    public function getName(): string
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
    public function getContext(): array
    {
        return $this->_context;
    }

    public function getContextParam($name, $default = null)
    {
        return $this->_context[$name] ?? $default;
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
     * @param \Throwable $e
     *
     * @return string
     * @todo Rewrite to ExceptionHandler and move exception handling logic to it
     */
    protected function processException(\Throwable $e): string
    {
        try {
            return (string)WidgetException::_handler($e);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Default action for Controller_Widget
     *
     * @deprecated Rewrite widget controller to direct Widget::render() call
     */
    public function action_render(): void
    {
        $this->_render();
    }

    /**
     * Renders widget View
     *
     * @return string
     */
    public function render(): string
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
    protected function _render(): void
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
        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        // Sending View to output
        $this->send_view($view);
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        return [];
    }

    /**
     * @param string $current_state
     */
    public function setCurrentState($current_state): void
    {
        $this->_current_state = $current_state;
    }

    /**
     * @return string
     */
    public function getCurrentState(): string
    {
        return $this->_current_state;
    }

    /**
     * @param null $action
     * @param bool $protocol
     *
     * @return string
     * @deprecated
     */
    protected function url($action = null, $protocol = null): string
    {
        return Route::url(
            'widget-controller',
            ['widget' => $this->getName(), 'action' => $action],
            $protocol ?? true
        );
    }

    /**
     * @param string|null $file
     *
     * @return \View
     */
    protected function view($file = null): \View
    {
        if (!$file) {
            $name = $this->getViewName();

            $suffix = $this->_current_state !== static::DEFAULT_STATE
                ? '-'.$this->_current_state
                : '';

            $file = str_replace('_', DIRECTORY_SEPARATOR, $name).$suffix;
        }

        $viewPath = 'widgets'.DIRECTORY_SEPARATOR.$file;

        return $this->view_factory($viewPath, $this->getContext());
    }

    protected function getViewName(): string
    {
        return $this->_name;
    }

    protected function getValidationErrors(Validation $validation): array
    {
        return $validation->errors($this->getValidationMessagesPath());
    }

    private function getValidationMessagesPath(): string
    {
        return 'widgets'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $this->_name);
    }

    /**
     * @param string     $message
     * @param array|null $variables
     *
     * @throws \BetaKiller\IFace\Widget\WidgetException
     */
    protected function throwException($message, ?array $variables = null): void
    {
        throw new WidgetException($message, $variables);
    }
}
