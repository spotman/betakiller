<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AbstractAssetsOrmImageModel;

class ContentImage extends AbstractAssetsOrmImageModel implements ContentImageInterface
{
    use OrmBasedContentElementEntityTrait,
        OrmBasedEntityHasWordpressIdTrait,
        OrmBasedEntityHasWordpressPathTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_table_name = 'content_images';

        $this->initialize_entity_relation();

        parent::_initialize();
    }
}
