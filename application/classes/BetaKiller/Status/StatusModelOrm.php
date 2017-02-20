<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphNodeModelInterface;
use BetaKiller\Graph\GraphTransitionModelInterface;
use BetaKiller\Graph\GraphNodeModelOrm;

abstract class StatusModelOrm extends GraphNodeModelOrm implements StatusModelInterface
{
    protected function _initialize()
    {
        $this->has_many([
            $this->get_related_model_key() => [
                'model'       => $this->get_related_model_name(),
                'foreign_key' => $this->get_related_model_fk(),
            ],
        ]);

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
     * @return StatusTransitionModelOrm
     */
    protected function transition_model_factory($id = NULL)
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
    public function get_allowed_transitions(GraphNodeModelInterface $source = NULL, GraphNodeModelInterface $target = NULL)
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
        return $this->get_allowed_transitions(NULL, $this);
    }

    /**
     * Returns list of target transitions allowed by ACL for current user
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_allowed_target_transitions()
    {
        return $this->get_allowed_transitions($this, NULL);
    }

    /**
     * @return string[]
     */
    public function get_allowed_target_transitions_codename_array()
    {
        $data = [];

        foreach ($this->get_allowed_target_transitions() as $transition) {
            $data[] = $transition->get_codename();
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

}
