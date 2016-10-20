<?php

class Model_ContentImageElement extends Assets_Model_ORM_Image implements Model_ContentElementInterface
{
    use Model_ORM_ContentElementTrait,
        Model_ORM_ImportedFromWordpressTrait,
        Model_ORM_HasWordpressPathTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'content_images';

        $this->initialize_entity_relation();

        parent::_initialize();
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return Assets_Provider_ContentImage
     */
    protected function get_provider()
    {
        return Assets_Provider_Factory::instance()->create('ContentImage');
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
     * @param string $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_alt($value)
    {
        return $this->set('alt', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_alt()
    {
        return $this->get('alt');
    }

    /**
     * @param string $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_title($value)
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_title()
    {
        return $this->get('title');
    }

    public function get_arguments_for_img_tag($size, array $attributes = [])
    {
        $attributes = array_merge([
            'alt'       =>  $this->get_alt(),
            'title'     =>  $this->get_title(),
        ], $attributes);

        return parent::get_arguments_for_img_tag($size, $attributes);
    }
}
