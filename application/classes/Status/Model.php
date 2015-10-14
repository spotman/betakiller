<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Status_Model extends Graph_Node_Model {

    protected function _initialize()
    {
        $this->has_many([
            $this->get_related_model_key()  =>  [
                'model'         =>  $this->get_related_model_name(),
                'foreign_key'   =>  $this->get_related_model_fk(),
            ]
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
     * @param null $id
     * @return Status_Transition_Model
     */
    protected function transition_model_factory($id = NULL)
    {
        return parent::transition_model_factory($id);
    }

    /**
     * Returns list of transitions allowed by ACL for current user
     *
     * @param Graph_Node_Model $source
     * @param Graph_Node_Model $target
     * @return Database_Result|Graph_Transition_Model[]
     */
    public function get_allowed_transitions(Graph_Node_Model $source = NULL, Graph_Node_Model $target = NULL)
    {
        return $this->transition_model_factory()->filter_allowed_by_acl()->get_transitions($source, $target);
    }

    /**
     * Returns list of source transitions allowed by ACL for current user
     *
     * @return Database_Result|Graph_Transition_Model[]
     */
    public function get_allowed_source_transitions()
    {
        return $this->get_allowed_transitions(NULL, $this);
    }

    /**
     * Returns list of target transitions allowed by ACL for current user
     *
     * @return Database_Result|Graph_Transition_Model[]
     */
    public function get_allowed_target_transitions()
    {
        return $this->get_allowed_transitions($this, NULL);
    }

    /**
     * @return array
     */
    public function get_allowed_target_transitions_codename_array()
    {
        return $this->get_allowed_target_transitions()->as_array(NULL, 'codename');
    }

    /**
     * Returns TRUE if target transition is allowed
     *
     * @param string $codename
     * @return bool
     */
    public function is_target_transition_allowed($codename)
    {
        $allowed = $this->get_allowed_target_transitions_codename_array();

        return in_array($codename, $allowed);
    }

    /**
     * @return Status_Related_Model
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
