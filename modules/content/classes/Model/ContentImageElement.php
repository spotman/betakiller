<?php

use BetaKiller\Content\ContentElementFromWordpressWithPathInterface;

class Model_ContentImageElement extends Assets_Model_ORM_SeoImage implements ContentElementFromWordpressWithPathInterface
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
}
