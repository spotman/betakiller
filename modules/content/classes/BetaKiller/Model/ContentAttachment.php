<?php
namespace BetaKiller\Model;

class ContentAttachment extends AbstractOrmBasedAssetsModel implements ContentAttachmentInterface
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
        $this->_table_name = 'content_attachments';

        $this->initializeEntityRelation();

        parent::_initialize();
    }

    /**
     * Returns true if content element has all required info
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasID() && $this->hasEntity() && $this->hasEntityItemID();
    }
}
