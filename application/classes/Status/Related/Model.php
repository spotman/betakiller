<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Status_Related_Model extends ORM {

    protected function _initialize()
    {
        $status_relation_key = $this->get_status_relation_key();

        $this->belongs_to([
            $status_relation_key    =>  [
                'model'             =>  $this->get_status_relation_model_name(),
                'foreign_key'       =>  $this->get_status_relation_foreign_key(),
            ]
        ]);

        parent::_initialize();
    }

    /**
     * @return Status_Model
     */
    public function get_current_status()
    {
        return $this->get_status_relation();
    }

    public function change_status(Status_Model $target)
    {
        $current = $this->get_current_status();

        if ( ! $current->has_target($target) )
            throw new Status_Exception('Target status is not allowed');

        return $this->set_current_status($target);
    }

    /**
     * @return Status_Model[]|NULL
     */
    public function get_allowed_statuses()
    {
        return $this->get_current_status()->get_target_nodes();
    }

    /**
     * @return Status_Model|NULL
     */
    public function set_start_status()
    {
        /** @var Status_Model $start */
        $start = $this->status_model_factory()->get_start_node();

        return $this->set_current_status($start);
    }

    /**
     * @param integer $status_id
     * @return $this
     */
    public function filter_status($status_id)
    {
        return $this->where($this->get_status_relation_foreign_key(), '=', $status_id);
    }

    public function get_status_id()
    {
        return $this->get($this->get_status_relation_foreign_key());
    }

    /**
     * @param int|array|NULL $id
     * @return Status_Model
     */
    public function status_model_factory($id = NULL)
    {
        return ORM::factory($this->get_status_relation_model_name(), $id);
    }

    /**
     * @return Status_Model
     */
    protected function get_status_relation()
    {
        return $this->get($this->get_status_relation_key());
    }

    /**
     * @param Status_Model $target
     * @return $this
     */
    protected function set_current_status(Status_Model $target)
    {
        return $this->set($this->get_status_relation_key(), $target);
    }


    protected function get_status_relation_key()
    {
        return 'status';
    }

    /**
     * @return string
     */
    abstract protected function get_status_relation_model_name();

    /**
     * @return string
     */
    abstract protected function get_status_relation_foreign_key();

}