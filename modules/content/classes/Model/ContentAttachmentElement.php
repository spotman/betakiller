<?php

use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Model\AbstractAssetsOrmModel;
use BetaKiller\Content\WordpressAttachmentInterface;

class Model_ContentAttachmentElement extends AbstractAssetsOrmModel implements WordpressAttachmentInterface
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
    protected function getProvider()
    {
        return AssetsProviderFactory::instance()->create('ContentAttachment');
    }
}
