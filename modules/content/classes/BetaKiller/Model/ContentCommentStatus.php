<?php
namespace BetaKiller\Model;

use BetaKiller\Status\StatusModelOrm;

class ContentCommentStatus extends StatusModelOrm
{
    public const STATUS_PENDING  = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_SPAM     = 3;
    public const STATUS_TRASH    = 4;

    protected function _initialize(): void
    {
        $this->_table_name = 'content_comment_statuses';

        parent::_initialize();
    }

    /**
     * @return string
     */
    protected function getStatusAclModelName(): string
    {
        return 'ContentCommentStatusAcl';
    }

    /**
     * @return string
     */
    protected function getStatusAclModelForeignKey(): string
    {
        return 'status_id';
    }

    public function getLabel(): string
    {
        $codename = $this->getCodename();

        return __('comment.status.'.$codename);
    }

    /**
     * @return string
     */
    protected function getRelatedModelKey(): string
    {
        return 'status';
    }

    /**
     * @return string
     */
    protected function getRelatedModelName(): string
    {
        return 'ContentComment';
    }

    /**
     * @return string
     */
    protected function getTransitionModelName(): string
    {
        return 'ContentCommentStatusTransition';
    }

    /**
     * @return string
     */
    protected function getRelatedModelFk(): string
    {
        return 'status_id';
    }
}
