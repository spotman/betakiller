<?php
namespace BetaKiller\Widget;

use BetaKiller\Utils\Kohana\ControllerHelperTrait;
use Route;
use Validation;

abstract class AbstractWidget implements WidgetInterface
{
    use ControllerHelperTrait;

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

    protected function getValidationErrors(Validation $validation): array
    {
        return $validation->errors($this->getValidationMessagesPath());
    }

    private function getValidationMessagesPath(): string
    {
        return 'widgets'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $this->name);
    }
}
