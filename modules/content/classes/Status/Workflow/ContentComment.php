<?php

use BetaKiller\Notification\NotificationUserEmail;
use BetaKiller\Status\StatusWorkflow;
use BetaKiller\Status\StatusWorkflowException;
use BetaKiller\Helper\InProductionTrait;
use BetaKiller\Notification\NotificationMessageCommon;

class Status_Workflow_ContentComment extends StatusWorkflow
{
    use InProductionTrait;

    const TRANSITION_APPROVE    = 'approve';
    const TRANSITION_REJECT     = 'reject';
    const TRANSITION_SPAM       = 'spam';
    const TRANSITION_TRASH      = 'trash';
    const TRANSITION_RESTORE    = 'restore';

    public function draft()
    {
        if ($this->model()->has_current_status()) {
            throw new StatusWorkflowException('Can not mark comment [:id] as draft coz it is in [:status] status', [
                ':id'       =>  $this->model()->get_id(),
                ':status'   =>  $this->model()->get_current_status()->get_codename()
            ]);
        }

        $this->model()->set_start_status();
    }

    public function approve()
    {
        $this->doTransition(self::TRANSITION_APPROVE);

        $comment = $this->model();

        // Notify comment author
        $this->notify_comment_author_about_approve($comment);

        // Notify parent comment author
        $this->notify_parent_comment_author_about_reply($comment);
    }

    protected function notify_comment_author_about_approve(Model_ContentComment $comment)
    {
        $email = $comment->get_author_email();
        $created_at = $comment->get_created_at()->format('H:i:s d.m.Y');
        $content_label = $comment->get_related_content_label();

        $data = [
            'name'          =>  $comment->get_author_name(),
            'url'           =>  $comment->get_public_url(),
            'created_at'    =>  $created_at,
            'label'         =>  $content_label,
        ];

        $subj = __('notification.comment.author-approve.subj', [
            ':name'         =>  $data['name'],
            ':url'          =>  $data['url'],
            ':label'        =>  $data['label'],
            ':created_at'   =>  $data['created_at'],
        ]);

        $message = NotificationMessageCommon::instance();

        $message
            ->set_subj($subj)
            ->set_template_name('user/comment/author-approve')
            ->set_template_data($data);

        if ($this->in_production()) {
            $to = NotificationUserEmail::factory($email);
            $message->set_to($to);
        } else {
            $message->to_current_user();
        }

        $message->send();
    }

    /**
     * @param \Model_ContentComment $reply
     */
    protected function notify_parent_comment_author_about_reply(Model_ContentComment $reply)
    {
        // Skip if parent comment email is equal to reply email

        // TODO Notify parent comment author
    }

    public function reject()
    {
        // Simply change status
        $this->doTransition(self::TRANSITION_REJECT);
    }

    public function spam()
    {
        // Simply change status
        $this->doTransition(self::TRANSITION_SPAM);
    }

    public function trash()
    {
        // Simply change status
        $this->doTransition(self::TRANSITION_TRASH);
    }

    public function restore()
    {
        // Simply change status
        $this->doTransition(self::TRANSITION_RESTORE);
    }

    /**
     * @return \Model_ContentComment|\BetaKiller\Status\StatusRelatedModelInterface
     */
    protected function model()
    {
        return $this->model;
    }
}
