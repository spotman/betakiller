<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\AbstractGraphNodeModelOrm;
use BetaKiller\Graph\GraphNodeModelInterface;
use BetaKiller\Graph\GraphTransitionModelInterface;
use BetaKiller\Model\UserInterface;

abstract class StatusModelOrm extends AbstractGraphNodeModelOrm implements StatusModelInterface
{
    protected const STATUS_ACL_RELATION_KEY = 'status_acl';

    /**
     * @throws \Exception
     */
    protected function _initialize()
    {
        $this->has_many([
            $this->getRelatedModelKey() => [
                'model'       => $this->getRelatedModelName(),
                'foreign_key' => $this->getRelatedModelFk(),
            ],
        ]);

        if ($this->isStatusAclEnabled()) {
            $this->has_many([
                static::STATUS_ACL_RELATION_KEY => [
                    'model'       => $this->getStatusAclModelName(),
                    'foreign_key' => $this->getStatusAclModelForeignKey(),
                ],
            ]);
        }

        parent::_initialize();
    }

    /**
     * @return int
     * @throws \Kohana_Exception
     */
    public function getRelatedCount(): int
    {
        return $this->getRelatedModelRelation()->count_all();
    }

    /**
     * @return StatusRelatedModelInterface[]
     * @throws \Kohana_Exception
     */
    public function getRelatedList(): array
    {
        return $this->getRelatedModelRelation()->get_all();
    }

    /**
     * @param null $id
     *
     * @return StatusTransitionModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function transitionModelFactory($id = null)
    {
        return parent::transitionModelFactory($id);
    }

    /**
     * Returns list of transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param GraphNodeModelInterface|null    $source
     * @param GraphNodeModelInterface|null    $target
     *
     * @return GraphTransitionModelInterface[]
     * @throws \Kohana_Exception
     */
    public function getAllowedTransitions(
        UserInterface $user,
        ?GraphNodeModelInterface $source = null,
        ?GraphNodeModelInterface $target = null
    ): array {
        return $this->transitionModelFactory()->filterAllowedByAcl($user)->getTransitions($source, $target);
    }

    /**
     * Returns list of source transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return GraphTransitionModelInterface[]
     */
    public function getAllowedSourceTransitions(UserInterface $user): array
    {
        return $this->getAllowedTransitions($user, null, $this);
    }

    /**
     * Returns list of target transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return GraphTransitionModelInterface[]
     */
    public function getAllowedTargetTransitions(UserInterface $user): array
    {
        return $this->getAllowedTransitions($user, $this);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string[]
     */
    public function getAllowedTargetTransitionsCodenameArray(UserInterface $user): array
    {
        $data = [];

        foreach ($this->getAllowedTargetTransitions($user) as $transition) {
            $targetStatusCodename        = $transition->getTargetNode()->getCodename();
            $data[$targetStatusCodename] = $transition->getCodename();
        }

        return $data;
    }

    /**
     * Returns TRUE if target transition is allowed
     *
     * @param string                          $codename
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isTargetTransitionAllowed(string $codename, UserInterface $user): bool
    {
        $allowed = $this->getAllowedTargetTransitionsCodenameArray($user);

        return \in_array($codename, $allowed, true);
    }

    /**
     * Returns true if status-based ACL is enabled (needs *StatusAcl model + *_status_acl table)
     *
     * @return bool
     */
    public function isStatusAclEnabled(): bool
    {
        return (bool)$this->getStatusAclModelName();
    }

    /**
     * @param string $action
     *
     * @return string[]
     * @throws \Kohana_Exception
     * @throws \BetaKiller\Status\StatusException
     */
    public function getStatusActionAllowedRoles(string $action): array
    {
        $this->checkIsStatusAclEnabled();

        return $this->getStatusAclRelation()->getActionAllowedRoles($action);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     */
    protected function checkIsStatusAclEnabled(): void
    {
        if (!$this->isStatusAclEnabled()) {
            throw new StatusException('Status ACL disabled for model :name', [
                ':name' => static::class,
            ]);
        }
    }

    /**
     * @return \BetaKiller\Status\StatusAclModelInterface
     * @throws \Kohana_Exception
     */
    protected function getStatusAclRelation(): StatusAclModelInterface
    {
        return $this->get(static::STATUS_ACL_RELATION_KEY);
    }

    /**
     * @return StatusRelatedModelOrm
     * @throws \Kohana_Exception
     */
    protected function getRelatedModelRelation(): StatusRelatedModelOrm
    {
        return $this->get($this->getRelatedModelKey());
    }

    /**
     * @return string
     */
    abstract protected function getRelatedModelKey(): string;

    /**
     * @return string
     */
    abstract protected function getRelatedModelName(): string;

    /**
     * @return string
     */
    abstract protected function getRelatedModelFk(): string;

    /**
     * @return string
     */
    abstract protected function getStatusAclModelName(): string;

    /**
     * @return string
     */
    abstract protected function getStatusAclModelForeignKey(): string;
}
