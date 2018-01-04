<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphNodeModelInterface;
use BetaKiller\Graph\GraphNodeModelOrm;
use BetaKiller\Graph\GraphTransitionModelInterface;
use BetaKiller\Model\UserInterface;

abstract class StatusModelOrm extends GraphNodeModelOrm implements StatusModelInterface
{
    protected const STATUS_ACL_RELATION_KEY = 'status_acl';

    /**
     * @throws \Exception
     */
    protected function _initialize()
    {
        $this->has_many([
            $this->get_related_model_key() => [
                'model'       => $this->get_related_model_name(),
                'foreign_key' => $this->get_related_model_fk(),
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
    public function get_related_count(): int
    {
        return $this->get_related_model_relation()->count_all();
    }

    /**
     * @return StatusRelatedModelInterface[]
     * @throws \Kohana_Exception
     */
    public function get_related_list(): array
    {
        return $this->get_related_model_relation()->get_all();
    }

    /**
     * @param null $id
     *
     * @return StatusTransitionModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function transition_model_factory($id = null)
    {
        return parent::transition_model_factory($id);
    }

    /**
     * Returns list of transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param GraphNodeModelInterface|null $source
     * @param GraphNodeModelInterface|null $target
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_allowed_transitions(UserInterface $user, GraphNodeModelInterface $source = null, GraphNodeModelInterface $target = null): array
    {
        return $this->transition_model_factory()->filter_allowed_by_acl($user)->get_transitions($source, $target);
    }

    /**
     * Returns list of source transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_allowed_source_transitions(UserInterface $user): array
    {
        return $this->get_allowed_transitions($user, null, $this);
    }

    /**
     * Returns list of target transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_allowed_target_transitions(UserInterface $user): array
    {
        return $this->get_allowed_transitions($user, $this);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string[]
     */
    public function get_allowed_target_transitions_codename_array(UserInterface $user): array
    {
        $data = [];

        foreach ($this->get_allowed_target_transitions($user) as $transition) {
            $target_status_codename        = $transition->get_target_node()->get_codename();
            $data[$target_status_codename] = $transition->get_codename();
        }

        return $data;
    }

    /**
     * Returns TRUE if target transition is allowed
     *
     * @param string $codename
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function is_target_transition_allowed(string $codename, UserInterface $user): bool
    {
        $allowed = $this->get_allowed_target_transitions_codename_array($user);

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
    protected function get_related_model_relation(): StatusRelatedModelOrm
    {
        return $this->get($this->get_related_model_key());
    }

    /**
     * @return string
     */
    abstract protected function get_related_model_key(): string;

    /**
     * @return string
     */
    abstract protected function get_related_model_name(): string;

    /**
     * @return string
     */
    abstract protected function get_related_model_fk(): string;

    /**
     * @return string
     */
    abstract protected function getStatusAclModelName(): string;

    /**
     * @return string
     */
    abstract protected function getStatusAclModelForeignKey(): string;
}
