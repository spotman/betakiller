<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\AbstractWorkflowStateOrm;

class ContentCommentStatus extends AbstractWorkflowStateOrm
{
    public const STATUS_PENDING  = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_SPAM     = 3;
    public const STATUS_TRASH    = 4;

    protected function configure(): void
    {
        $this->_table_name = 'content_comment_statuses';
    }

    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return 'comment.status.'.$this->getCodename();
    }
}
