<?php
namespace BetaKiller\Widget;

interface WidgetInterface
{
    public const DEFAULT_STATE = 'default';

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
     * Returns array of roles` codenames which are allowed to use this widget
     *
     * @return array
     */
    public function getAclRoles(): array;

    /**
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool;

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
     * @return \BetaKiller\Widget\WidgetInterface
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
