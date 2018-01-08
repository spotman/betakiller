<?php
namespace BetaKiller\Widget;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Utils\Kohana\ControllerHelperTrait;
use BetaKiller\View\ViewFactoryInterface;
use BetaKiller\View\ViewInterface;
use Psr\Log\LoggerAwareTrait;
use Route;
use Validation;

abstract class AbstractWidget implements WidgetInterface
{
    use ControllerHelperTrait;
    use LoggerAwareTrait;
    use LoggerHelperTrait;

    private const DEFAULT_STATE = 'default';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string Current widget state (for Finite State Machine)
     */
    private $currentState = self::DEFAULT_STATE;

    /**
     * @var array Context for widget rendering
     */
    private $context = [];

    /**
     * @var \Request
     * @deprecated Inject PSR request in every action
     */
    protected $request;

    /**
     * @var \Response
     * @deprecated Inject PSR response prototype in every action
     */
    protected $response;

    /**
     * @Inject
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

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
     * @return \BetaKiller\Widget\WidgetInterface
     */
    public function setName(string $value): WidgetInterface
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Getter for widget name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Setter for widget context (additional data for rendering)
     *
     * @param array $value
     *
     * @return \BetaKiller\Widget\WidgetInterface
     */
    public function setContext(array $value): WidgetInterface
    {
        $this->context = $value;

        return $this;
    }

    /**
     * Getter for widget context (additional data for rendering)
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getContextParam($name, $default = null)
    {
        return $this->context[$name] ?? $default;
    }

    /**
     * @return \BetaKiller\View\ViewFactoryInterface
     */
    public function getViewFactory(): ViewFactoryInterface
    {
        return $this->viewFactory;
    }

    /**
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     *
     * @return \BetaKiller\Widget\WidgetInterface
     */
    public function setViewFactory(ViewFactoryInterface $viewFactory): WidgetInterface
    {
        $this->viewFactory = $viewFactory;

        return $this;
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
            $this->logException($this->logger, $e);

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
        $view = $this->prepareRender();

        $this->send_view($view);
    }

    /**
     * Renders widget View
     *
     * @return string
     */
    public function render(): string
    {
        return (string)$this->prepareRender();
    }

    /**
     * Generates HTML/CSS/JS representation of the widget
     */
    protected function prepareRender(): ViewInterface
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

        return $view;
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
        $this->currentState = $current_state;
    }

    /**
     * @return string
     */
    public function getCurrentState(): string
    {
        return $this->currentState;
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
     * @return \BetaKiller\View\ViewInterface
     */
    protected function view($file = null): ViewInterface
    {
        if (!$file) {
            $name = $this->getViewName();

            $suffix = $this->currentState !== self::DEFAULT_STATE
                ? '-'.$this->currentState
                : '';

            $file = str_replace('_', DIRECTORY_SEPARATOR, $name).$suffix;
        }

        $viewPath = 'widgets'.DIRECTORY_SEPARATOR.$file;

        return $this->viewFactory->create($viewPath, $this->getContext());
    }

    protected function getViewName(): string
    {
        return $this->name;
    }

    protected function getValidationErrors(Validation $validation): array
    {
        return $validation->errors($this->getValidationMessagesPath());
    }

    private function getValidationMessagesPath(): string
    {
        return 'widgets'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $this->name);
    }
}
