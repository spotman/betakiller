<?php

abstract class Model_ORM_ContentBase extends ORM implements \BetaKiller\Content\SeoContentInterface
{
    use Model_ORM_SeoContentTrait;

    /**
     * Marker for "updated_at" field change
     * Using this because of ORM::set() is checking value is really changed, but we may set the equal value
     *
     * @var bool
     */
    protected $updated_at_was_set = FALSE;

    abstract public function get_public_url();

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_uri($value)
    {
        return $this->set('uri', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_uri()
    {
        return $this->get('uri');
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_label($value)
    {
        return $this->set('label', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_label()
    {
        return $this->get('label');
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_content($value)
    {
        return $this->set('content', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_content()
    {
        return $this->get('content');
    }

    /**
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_created_at(DateTime $value)
    {
        return $this->set('created_at', $value->format('Y-m-d H:i:s'));
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function get_created_at()
    {
        $value = $this->get('created_at');

        return $value ? new DateTime($value) : NULL;
    }

    /**
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_updated_at(DateTime $value)
    {
        $this->updated_at_was_set = TRUE;

        return $this->set('updated_at', $value->format('Y-m-d H:i:s'));
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function get_updated_at()
    {
        $value = $this->get('updated_at');

        return $value ? new DateTime($value) : NULL;
    }

    /**
     * @return DateTime
     */
    public function get_last_modified()
    {
        return $this->get_updated_at() ?: $this->get_created_at();
    }

    /**
     * @return $this
     */
    public function increment_views_count()
    {
        $current = $this->get_views_count();

        return $this->set_views_count(++$current);
    }

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function get_views_count()
    {
        return (int) $this->get('views_count');
    }

    /**
     * @param int $value
     * @return $this
     * @throws Kohana_Exception
     */
    protected function set_views_count($value)
    {
        return $this->set('views_count', (int) $value);
    }

    public function order_by_views_count($asc = false)
    {
        return $this->order_by('views_count', $asc ? 'ASC' : 'DESC');
    }

    /**
     * @param int $limit
     *
     * @return \Database_Result|\Model_ORM_ContentBase[]
     */
    public function get_popular_content($limit = 5)
    {
        return $this->model_factory()->order_by_views_count()->limit($limit)->get_all();
    }

    /**
     * Insert a new object to the database
     * @param  Validation $validation Validation object
     * @throws Kohana_Exception
     * @return ORM
     */
    public function create(Validation $validation = NULL)
    {
        $this->set_created_at(new DateTime);

        return parent::create($validation);
    }

    /**
     * Updates a single record or multiple records
     *
     * @chainable
     * @param  Validation $validation Validation object
     * @throws Kohana_Exception
     * @return ORM
     */
    public function update(Validation $validation = NULL)
    {
        if ($this->changed() AND !$this->updated_at_was_set)
        {
            $this->set_updated_at(new DateTime);
        }

        return parent::update($validation);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function filter_uri($value)
    {
        return $this->where($this->object_column('uri'), '=', $value);
    }

}
