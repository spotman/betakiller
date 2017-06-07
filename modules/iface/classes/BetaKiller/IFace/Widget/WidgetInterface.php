<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\Utils\Kohana\Request;
use BetaKiller\Utils\Kohana\Response;

interface WidgetInterface
{
    /**
     * Setter for widget name
     *
     * @param string $value
     * @return $this
     */
    public function setName($value);

    /**
     * Getter for widget name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * @param \BetaKiller\Utils\Kohana\Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request);

    /**
     * @param \BetaKiller\Utils\Kohana\Response $response
     *
     * @return $this
     */
    public function setResponse(Response $response);

    /**
     * Setter for widget context (additional data for rendering)
     *
     * @param array $value
     * @return $this
     */
    public function setContext(array $value);

    /**
     * Getter for widget context (additional data for rendering)
     *
     * @return array
     */
    public function getContext(): array;

    /**
     * @param string $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getContextParam($name, $default = null);

    /**
     * Renders widget and returns its representation
     * @return string
     */
    public function __toString();

    /**
     * Default action for Controller_Widget
     */
    public function action_render(): void;

    /**
     * Renders widget View
     *
     * @return string
     */
    public function render(): string;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array;

    /**
     * @param string $currentState
     */
    public function setCurrentState($currentState): void;

    /**
     * @return string
     */
    public function getCurrentState(): string;
}
