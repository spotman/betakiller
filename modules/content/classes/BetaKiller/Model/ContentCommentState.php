<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\AbstractWorkflowStateOrm;

class ContentCommentState extends AbstractWorkflowStateOrm
{
    public const PENDING  = 'pending';
    public const APPROVED = 'approved';
    public const SPAM     = 'spam';
    public const TRASH    = 'trash';

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
