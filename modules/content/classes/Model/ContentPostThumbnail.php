<?php

class Model_ContentPostThumbnail extends Assets_Model_ORM_Image
{
    use Model_ORM_ContentImageElementTrait,
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
        $this->_table_name = 'content_post_thumbnails';

        parent::_initialize();
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return Assets_Provider_ContentThumbnail
     */
    protected function get_provider()
    {
        return Assets_Provider_Factory::instance()->create('ContentThumbnail');
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
}
