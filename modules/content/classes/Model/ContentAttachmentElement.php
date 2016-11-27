<?php

use BetaKiller\Content\ContentElementFromWordpressWithPathInterface;

class Model_ContentAttachmentElement extends Assets_Model_ORM implements ContentElementFromWordpressWithPathInterface
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
        $this->_table_name = 'content_attachments';

        $this->initialize_entity_relation();

        parent::_initialize();
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return Assets_Provider_ContentAttachment
     */
    protected function get_provider()
    {
        return Assets_Provider_Factory::instance()->create('ContentAttachment');
    }
}
