<?php defined('SYSPATH') OR die('No direct script access.');


class ORM extends Kohana_ORM implements API_Response_Item /* , DataSource_Interface */ {

    public function belongs_to(array $config = NULL)
    {
        if ( $config )
        {
            $this->_belongs_to = array_merge($this->_belongs_to, $config);
        }

        return parent::belongs_to();
    }

    public function has_one(array $config = NULL)
    {
        if ( $config )
        {
            $this->_has_one = array_merge($this->_has_one, $config);
        }

        return parent::has_one();
    }

    public function has_many(array $config = NULL)
    {
        if ( $config )
        {
            $this->_has_many = array_merge($this->_has_many, $config);
        }

        return parent::has_many();
    }

    public function load_with(array $config = NULL)
    {
        if ( $config )
        {
            $this->_load_with = array_merge($this->_load_with, $config);
        }

        return parent::load_with();
    }

    /**
     * Default implementation for ORM objects
     * Override this method in child classes
     *
     * @return array
     */
    public function get_api_response_data()
    {
        return $this->as_array();
    }

    /**
     * @return NULL
     */
    public function get_last_modified()
    {
        // Empty by default
        return NULL;
    }

    /**
     * @return $this
     */
    public function randomize()
    {
        return $this->order_by(DB::expr('RAND()'));
    }

    public function autocomplete($query, $search_column)
    {
        $pk_column = $this->primary_key();

        /** @var Database_Result $query */
        $query = DB::select(array($pk_column, $search_column))
            ->from($this->table_name())
            ->where($search_column, 'LIKE', $query.'%')
            ->compile();
//            ->execute();

        die($query);

        return $query->as_array($pk_column, $search_column);
    }

}