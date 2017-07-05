<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AbstractAssetsOrmModel;
use BetaKiller\Content\ContentAttachmentInterface;
use Model_ORM_ContentElementEntityTrait;
use Model_ORM_EntityHasWordpressIdTrait;
use Model_ORM_EntityHasWordpressPathTrait;

class ContentAttachment extends AbstractAssetsOrmModel implements ContentAttachmentInterface
{
    use Model_ORM_ContentElementEntityTrait,
        Model_ORM_EntityHasWordpressIdTrait,
        Model_ORM_EntityHasWordpressPathTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_table_name = 'content_attachments';

        $this->initialize_entity_relation();

        parent::_initialize();
    }
}
