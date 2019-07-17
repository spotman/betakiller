<?php
declare(strict_types=1);

namespace BetaKiller\Config;

use BetaKiller\Workflow\StatusWorkflowException;

class WorkflowConfig extends AbstractConfig implements WorkflowConfigInterface
{
    public const STATES       = 'states';
    public const STATUS_MODEL = 'model';
    public const TRANSITIONS  = 'transitions';
    public const ACTIONS      = 'actions';
    public const IS_START     = 'is_start';
    public const IS_FINISH    = 'is_finish';

    private const ROOT              = 'workflows';
    private const PATH_MODELS       = [];
    private const PATH_STATUS_MODEL = [self::KEY_MODEL => null, self::STATUS_MODEL];
    private const PATH_STATES       = [self::KEY_MODEL => null, self::STATES];

    private const PATH_STATE_IS_START = [
        self::KEY_MODEL => null,
        self::STATES,
        self::KEY_STATE => null,
        self::IS_START,
    ];

    private const PATH_STATE_IS_FINISH = [
        self::KEY_MODEL => null,
        self::STATES,
        self::KEY_STATE => null,
        self::IS_FINISH,
    ];

    private const PATH_STATE_ACTIONS = [
        self::KEY_MODEL => null,
        self::STATES,
        self::KEY_STATE => null,
        self::ACTIONS,
    ];

    private const PATH_STATE_ACTION_ROLES = [
        self::KEY_MODEL  => null,
        self::STATES,
        self::KEY_STATE  => null,
        self::ACTIONS,
        self::KEY_ACTION => null,
    ];

    private const PATH_STATE_TARGET_TRANSITIONS = [
        self::KEY_MODEL => null,
        self::STATES,
        self::KEY_STATE => null,
        self::TRANSITIONS,
    ];

    private const PATH_TRANSITIONS = [
        self::KEY_MODEL => null,
        self::TRANSITIONS,
    ];

    private const PATH_TRANSITION_ROLES = [
        self::KEY_MODEL      => null,
        self::TRANSITIONS,
        self::KEY_TRANSITION => null,
    ];

    // Fake keys for path management
    private const KEY_MODEL      = 'modelName';
    private const KEY_STATE      = 'stateName';
    private const KEY_ACTION     = 'actionName';
    private const KEY_TRANSITION = 'transitionName';

    /**
     * Returns models` names
     *
     * @return string[]
     */
    public function getModels(): array
    {
        return array_keys((array)$this->get(self::PATH_MODELS));
    }

    /**
     * Returns name of related workflow status model
     *
     * @param string $model
     *
     * @return string
     */
    public function getStateModelName(string $model): string
    {
        return (string)$this->get(array_merge(self::PATH_STATUS_MODEL, [
            self::KEY_MODEL => $model,
        ]));
    }

    /**
     * Returns States` names
     *
     * @param string $model
     *
     * @return string[]
     */
    public function getStates(string $model): array
    {
        return array_keys((array)$this->get(array_merge(self::PATH_STATES, [
            self::KEY_MODEL => $model,
        ])));
    }

    /**
     * Returns TRUE if workflow is starting from the provided state
     *
     * @param string $model
     * @param string $state
     *
     * @return bool
     */
    public function isStartState(string $model, string $state): bool
    {
        return (bool)$this->get(array_merge(self::PATH_STATE_IS_START, [
            self::KEY_MODEL => $model,
            self::KEY_STATE => $state,
        ]), true);
    }

    /**
     * Returns TRUE if workflow is ending in the provided state
     *
     * @param string $model
     * @param string $state
     *
     * @return bool
     */
    public function isFinishState(string $model, string $state): bool
    {
        return (bool)$this->get(array_merge(self::PATH_STATE_IS_FINISH, [
            self::KEY_MODEL => $model,
            self::KEY_STATE => $state,
        ]), true);
    }

    /**
     * Returns State Actions` names
     *
     * @param string $modelName
     * @param string $stateName
     *
     * @return string[]
     */
    public function getStateActions(string $modelName, string $stateName): array
    {
        return array_keys((array)$this->get(array_merge(self::PATH_STATE_ACTIONS, [
            self::KEY_MODEL => $modelName,
            self::KEY_STATE => $stateName,
        ])));
    }

    /**
     * Returns "Transition codename => target State codename" pairs for provided state
     *
     * @param string $model
     * @param string $state
     *
     * @return string[]
     */
    public function getStateTargetTransitions(string $model, string $state): array
    {
        return (array)$this->get(array_merge(self::PATH_STATE_TARGET_TRANSITIONS, [
            self::KEY_MODEL => $model,
            self::KEY_STATE => $state,
        ]), true);
    }

    /**
     * Returns target state name for provided state/transition pair
     *
     * @param string $model
     * @param string $state
     * @param string $transition
     *
     * @return string
     */
    public function getStateTransitionTarget(string $model, string $state, string $transition): string
    {
        foreach ($this->getStateTargetTransitions($model, $state) as $trans => $target) {
            if ($trans === $transition) {
                return $target;
            }
        }

        throw new StatusWorkflowException('Unknown transition ":trans" from state ":state" in ":model"', [
            ':model' => $model,
            ':state' => $state,
            ':trans' => $transition,
        ]);
    }

    /**
     * @param string $modelName
     * @param string $stateName
     *
     * @param string $actionName
     *
     * @return string[]
     */
    public function getStateActionRoles(string $modelName, string $stateName, string $actionName): array
    {
        return (array)$this->get(array_merge(self::PATH_STATE_ACTION_ROLES, [
            self::KEY_MODEL  => $modelName,
            self::KEY_STATE  => $stateName,
            self::KEY_ACTION => $actionName,
        ]), true);
    }

    /**
     * Returns Transitions` names
     *
     * @param string $model
     *
     * @return string[]
     */
    public function getTransitions(string $model): array
    {
        return array_keys((array)$this->get(array_merge(self::PATH_TRANSITIONS, [
            self::KEY_MODEL => $model,
        ])));
    }

    /**
     * @param string $model
     * @param string $transition
     *
     * @return string[]
     */
    public function getTransitionRoles(string $model, string $transition): array
    {
        return (array)$this->get(array_merge(self::PATH_TRANSITION_ROLES, [
            self::KEY_MODEL      => $model,
            self::KEY_TRANSITION => $transition,
        ]));
    }

    protected function getConfigRootGroup(): string
    {
        return self::ROOT;
    }
}
