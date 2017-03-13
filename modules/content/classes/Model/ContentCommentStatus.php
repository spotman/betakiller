<?php

use BetaKiller\Status\StatusModelOrm;

class Model_ContentCommentStatus extends StatusModelOrm
{
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_SPAM = 3;
    const STATUS_TRASH = 4;

    const URL_PARAM = 'ContentCommentStatus';

    protected $_table_name = 'content_comment_statuses';

    public function getLabel()
    {
        $codename = $this->get_codename();
        return __('comment.status.'.$codename);
    }

    /**
     * @return Model_ContentCommentStatus
     */
    public function get_pending_status()
    {
        return $this->model_factory(self::STATUS_PENDING);
    }

    /**
     * @return Model_ContentCommentStatus
     */
    public function get_approved_status()
    {
        return $this->model_factory(self::STATUS_APPROVED);
    }

    /**
     * @return Model_ContentCommentStatus
     */
    public function get_spam_status()
    {
        return $this->model_factory(self::STATUS_SPAM);
    }

    /**
     * @return Model_ContentCommentStatus
     */
    public function get_trash_status()
    {
        return $this->model_factory(self::STATUS_TRASH);
    }

    /**
     * @return string
     */
    protected function get_related_model_key()
    {
        return 'status';
    }

    /**
     * @return string
     */
    protected function get_related_model_name()
    {
        return 'ContentComment';
    }

    /**
     * @return string
     */
    protected function get_transition_model_name()
    {
        return 'ContentCommentStatusTransition';
    }

    /**
     * @return string
     */
    protected function get_related_model_fk()
    {
        return 'status_id';
    }
}
