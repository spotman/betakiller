<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Status_Model extends Graph_Node_Model {

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

}
