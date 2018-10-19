<?php
namespace BetaKiller\Widget;

use Psr\Http\Message\ServerRequestInterface;

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
     * Returns name of the view (underscores instead of directory separator)
     *
     * @return string
     */
    public function getViewName(): string;

    /**
     * Returns array of roles` codenames which are allowed to use this widget
     *
     * @return string[]
     */
    public function getAclRoles(): array;

    /**
     * Returns true if current widget may be omitted during the render process
     *
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool;

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request, array $context): array;

    /**
     * @param string $currentState
     */
    public function setCurrentState(string $currentState): void;

    /**
     * @return string
     */
    public function getCurrentState(): string;
}
