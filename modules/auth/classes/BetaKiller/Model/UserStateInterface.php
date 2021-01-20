<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

interface UserStateInterface extends WorkflowStateInterface
{
    /**
     * @return bool
     */
    public function isCreated(): bool;

    /**
     * @return bool
     */
    public function isConfirmed(): bool;

    /**
     * @return bool
     */
    public function isEmailChanged(): bool;

    /**
     * @return bool
     */
    public function isBlocked(): bool;

    /**
     * @return bool
     */
    public function isSuspended(): bool;

    /**
     * @return bool
     */
    public function isResumed(): bool;
}
