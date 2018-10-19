<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

abstract class AbstractWidget implements WidgetInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string Current widget state (for Finite State Machine)
     */
    private $currentState = self::DEFAULT_STATE;

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
     * Returns name of the view (underscores instead of directory separator)
     *
     * @return string
     */
    public function getViewName(): string
    {
        // View name is equal to widget name by default
        return $this->getName();
    }

    /**
     * @param string $currentState
     */
    public function setCurrentState(string $currentState): void
    {
        $this->currentState = $currentState;
    }

    /**
     * @return string
     */
    public function getCurrentState(): string
    {
        return $this->currentState;
    }
}
