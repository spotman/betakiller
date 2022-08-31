<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CreatedByAtInterface extends CreatedAtInterface
{
    public const API_KEY_CREATED_BY = 'created_by';

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\CreatedByAtInterface
     */
    public function setCreatedBy(UserInterface $user): CreatedByAtInterface;

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getCreatedBy(): UserInterface;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isCreatedBy(UserInterface $user): bool;
}
