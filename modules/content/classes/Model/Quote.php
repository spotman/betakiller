<?php

class Model_Quote extends ORM
{
    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_text($value)
    {
        return $this->set('text', (string) $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_text()
    {
        return $this->get('text');
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_author($value)
    {
        return $this->set('author', (string) $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_author()
    {
        return $this->get('author');
    }

    public function set_created_at(DateTime $time)
    {
        return $this->set_datetime_column_value('created_at', $time);
    }

    public function get_created_at()
    {
        return $this->get_datetime_column_value('created_at');
    }

    public function order_by_created_at($asc = FALSE)
    {
        return $this->order_by('created_at', $asc ? 'ASC' : 'DESC');
    }

    public function filter_before_created_at(DateTime $before)
    {
        return $this->filter_datetime_column_value('created_at', $before, '<');
    }

    /**
     * @param \DateTime|NULL $before
     *
     * @return $this
     */
    public function get_latest_quote(DateTime $before = NULL)
    {
        $model = $this->model_factory();

        if ($before) {
            $model->filter_before_created_at($before);
        }

        return $model->order_by_created_at()->limit(1)->find();
    }
}
