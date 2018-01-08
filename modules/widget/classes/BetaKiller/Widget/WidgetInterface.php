<?php
namespace BetaKiller\Widget;

use BetaKiller\View\ViewFactoryInterface;
use Psr\Log\LoggerAwareInterface;

interface WidgetInterface extends LoggerAwareInterface
{
    /**
     * Setter for widget name
     *
     * @param string $value
     *
     * @return $this
     */
    public function setName(string $value): WidgetInterface;

    /**
     * Getter for widget name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * @param \Request $request
     *
     * @return $this|mixed
     * @deprecated Inject PSR request into each action (every action acts like PSR handler)
     */
    public function setRequest(\Request $request);

    /**
     * @param \Response $response
     *
     * @return $this|mixed
     * @deprecated Inject PSR response into each action (every action acts like PSR handler)
     */
    public function setResponse(\Response $response);

    /**
     * Setter for widget context (additional data for rendering)
     *
     * @param array $value
     *
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     */
    public function setContext(array $value): WidgetInterface;

    /**
     * Getter for widget context (additional data for rendering)
     *
     * @return array
     */
    public function getContext(): array;

    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getContextParam($name, $default = null);

    /**
     * @return \BetaKiller\View\ViewFactoryInterface
     */
    public function getViewFactory(): ViewFactoryInterface;

    /**
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     *
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     */
    public function setViewFactory(ViewFactoryInterface $viewFactory): WidgetInterface;

    /**
     * Renders widget and returns its representation
     *
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
