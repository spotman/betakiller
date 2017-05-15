<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphNodeModelInterface;
use BetaKiller\Graph\GraphNodeModelOrm;
use BetaKiller\Graph\GraphTransitionModelInterface;

abstract class StatusModelOrm extends GraphNodeModelOrm implements StatusModelInterface
{
    const STATUS_ACL_RELATION_KEY = 'status_acl';

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
     */
    public function get_related_count()
    {
        return $this->get_related_model_relation()->count_all();
    }

    /**
     * @return StatusRelatedModelInterface[]
     */
    public function get_related_list()
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
     * @param GraphNodeModelInterface $source
     * @param GraphNodeModelInterface $target
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_allowed_transitions(GraphNodeModelInterface $source = null, GraphNodeModelInterface $target = null)
    {
        return $this->transition_model_factory()->filter_allowed_by_acl()->get_transitions($source, $target);
    }

    /**
     * Returns list of source transitions allowed by ACL for current user
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_allowed_source_transitions()
    {
        return $this->get_allowed_transitions(null, $this);
    }

    /**
     * Returns list of target transitions allowed by ACL for current user
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_allowed_target_transitions()
    {
        return $this->get_allowed_transitions($this, null);
    }

    /**
     * @return string[]
     */
    public function get_allowed_target_transitions_codename_array()
    {
        $data = [];

        foreach ($this->get_allowed_target_transitions() as $transition) {
            $target_status_codename        = $transition->get_target_node()->get_codename();
            $data[$target_status_codename] = $transition->get_codename();
        }

        return $data;
    }

    /**
     * Returns TRUE if target transition is allowed
     *
     * @param string $codename
     *
     * @return bool
     */
    public function is_target_transition_allowed($codename)
    {
        $allowed = $this->get_allowed_target_transitions_codename_array();

        return in_array($codename, $allowed);
    }

    /**
     * Returns true if status-based ACL is enabled (needs *StatusAcl model + *_status_acl table)
     *
     * @return bool
     */
    public function isStatusAclEnabled()
    {
        return (bool)$this->getStatusAclModelName();
    }

    /**
     * @param string $action
     *
     * @return string[]
     */
    public function getStatusActionAllowedRoles($action)
    {
        $this->checkIsStatusAclEnabled();

        return $this->getStatusAclRelation()->getActionAllowedRoles($action);
    }

    protected function checkIsStatusAclEnabled()
    {
        if (!$this->isStatusAclEnabled()) {
            throw new StatusException('Status ACL disabled for model :name', [
                ':name' => static::class,
            ]);
        }
    }

    /**
     * @return \BetaKiller\Status\StatusAclModelInterface
     */
    protected function getStatusAclRelation()
    {
        return $this->get(static::STATUS_ACL_RELATION_KEY);
    }

    /**
     * @return StatusRelatedModelOrm
     */
    protected function get_related_model_relation()
    {
        return $this->get($this->get_related_model_key());
    }

    /**
     * @return string
     */
    abstract protected function get_related_model_key();

    /**
     * @return string
     */
    abstract protected function get_related_model_name();

    /**
     * @return string
     */
    abstract protected function get_related_model_fk();

    /**
     * @return string
     */
    abstract protected function getStatusAclModelName();

    /**
     * @return string
     */
    abstract protected function getStatusAclModelForeignKey();
}
