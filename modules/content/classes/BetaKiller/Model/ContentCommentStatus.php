<?php
namespace BetaKiller\Model;

use BetaKiller\Status\StatusModelOrm;

class ContentCommentStatus extends StatusModelOrm
{
    const STATUS_PENDING  = 1;
    const STATUS_APPROVED = 2;
    const STATUS_SPAM     = 3;
    const STATUS_TRASH    = 4;

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
        $codename = $this->get_codename();

        return __('comment.status.'.$codename);
    }

    /**
     * @return string
     */
    protected function get_related_model_key(): string
    {
        return 'status';
    }

    /**
     * @return string
     */
    protected function get_related_model_name(): string
    {
        return 'ContentComment';
    }

    /**
     * @return string
     */
    protected function get_transition_model_name(): string
    {
        return 'ContentCommentStatusTransition';
    }

    /**
     * @return string
     */
    protected function get_related_model_fk(): string
    {
        return 'status_id';
    }
}
