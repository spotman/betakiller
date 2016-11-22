<?php

class Model_ContentYoutubeRecord extends ORM
{
    use Model_ORM_ContentElementTrait,
        Model_ORM_ImportedFromWordpressTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'content_youtube_records';

        $this->initialize_entity_relation();

        $this->belongs_to(array(
            'uploaded_by_user'  =>  array(
                'model'         =>  'User',
                'foreign_key'   =>  'uploaded_by',
            )
        ));

        parent::_initialize();
    }

//    /**
//     * Rule definitions for validation
//     *
//     * @return array
//     */
//    public function rules()
//    {
//        return parent::rules() + [
//            'alt'   =>  [
//                ['not_empty']
//            ],
//        ];
//    }

    public function get_youtube_embed_url()
    {
        return 'https://www.youtube.com/embed/'.$this->get_youtube_id();
    }

    /**
     * @param string $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_youtube_id($value)
    {
        return $this->set('youtube_id', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_youtube_id()
    {
        return $this->get('youtube_id');
    }

    /**
     * @param int $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_width($value)
    {
        return $this->set('width', (int) $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_width()
    {
        return $this->get('width');
    }

    /**
     * @param int $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_height($value)
    {
        return $this->set('height', (int) $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_height()
    {
        return $this->get('height');
    }

    /**
     * Returns User model, who uploaded the file
     *
     * @return Model_User
     */
    public function get_uploaded_by()
    {
        return $this->get('uploaded_by_user');
    }

    /**
     * Sets user, who uploaded the file
     *
     * @param Model_User $user
     * @return $this
     */
    public function set_uploaded_by(Model_User $user)
    {
        return $this->set('uploaded_by_user', $user);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function filter_youtube_id($value)
    {
        return $this->where('youtube_id', '=', $value);
    }

    /**
     * @param string $id
     * @return $this|null
     */
    public function find_by_youtube_id($id)
    {
        $model = $this->model_factory()->filter_youtube_id($id)->find();

        return $model->loaded() ? $model : null;
    }
}
