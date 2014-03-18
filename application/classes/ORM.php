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

    public function get_id()
    {
        return $this->pk();
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

    /**
     * @param string $term String to search for
     * @param array $search_columns Columns to search where
     * @return ORM[]
     */
    protected function search($term, array $search_columns)
    {
        $this->and_where_open();

        foreach ( $search_columns as $search_column )
        {
            $this->or_where($search_column, 'LIKE', '%'.$term.'%');
        }

        return $this->and_where_close()->find_all();
    }

    protected function _autocomplete($query, array $search_fields)
    {
        /** @var ORM[] $results */
        $results = $this->search($query, $search_fields);

        $output = array();

        foreach ( $results as $item )
        {
            $output[] = $item->autocomplete_formatter();
        }

        return $output;
    }

    /**
     * @throws Kohana_Exception
     * @return array
     */
    protected function autocomplete_formatter()
    {
        throw new Kohana_Exception('Implement autocomplete_formatter for class :class_name',
            array(':class_name' => get_class($this)));
    }
}