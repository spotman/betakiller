<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface WorkflowConfigInterface
{
    /**
     * @return string[]
     */
    public function getModels(): array;

    /**
     * Returns name of related workflow status model
     *
     * @param string $model
     *
     * @return string
     */
    public function getStateModelName(string $model): string;

    /**
     * Returns States` names
     *
     * @param string $model
     *
     * @return string[]
     */
    public function getStates(string $model): array;

    /**
     * Returns Transitions` names
     *
     * @param string $model
     *
     * @return string[]
     */
    public function getTransitions(string $model): array;

    /**
     * Returns TRUE if workflow is starting from the provided state
     *
     * @param string $model
     * @param string $state
     *
     * @return bool
     */
    public function isStartState(string $model, string $state): bool;

    /**
     * Returns TRUE if workflow is ending in the provided state
     *
     * @param string $model
     * @param string $state
     *
     * @return bool
     */
    public function isFinishState(string $model, string $state): bool;

    /**
     * Returns "Transition codename => target State codename" pairs for provided state
     *
     * @param string $model
     * @param string $state
     *
     * @return string[]
     */
    public function getStateTargetTransitions(string $model, string $state): array;

    /**
     * Returns target state name for provided state/transition pair
     *
     * @param string $model
     * @param string $state
     * @param string $transition
     *
     * @return string
     */
    public function getStateTransitionTarget(string $model, string $state, string $transition): string;

    /**
     * Returns State Actions` names
     *
     * @param string $model
     * @param string $state
     *
     * @return string[]
     */
    public function getStateActions(string $model, string $state): array;

    /**
     * @param string $model
     * @param string $state
     *
     * @param string $action
     *
     * @return string[]
     */
    public function getStateActionRoles(string $model, string $state, string $action): array;

    /**
     * @param string $model
     * @param string $transition
     *
     * @return string[]
     */
    public function getTransitionRoles(string $model, string $transition): array;
}
