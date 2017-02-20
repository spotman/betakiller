<?php

use BetaKiller\Notification\NotificationUserEmail;
use BetaKiller\Status\StatusWorkflow;
use BetaKiller\Status\StatusWorkflowException;
use BetaKiller\Helper\InProductionTrait;

class Status_Workflow_Content_Comment extends StatusWorkflow
{
    use InProductionTrait;

    const TRANSITION_APPROVE    = 'approve';
    const TRANSITION_REJECT     = 'reject';
    const TRANSITION_SPAM       = 'spam';
    const TRANSITION_DELETE     = 'delete';
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
        $this->do_transition(self::TRANSITION_APPROVE);

        $comment = $this->model();

        // Notify comment author
        $this->notify_comment_author_about_approve($comment);

        // Notify parent comment author
        $this->notify_parent_comment_author_about_reply($comment);
    }

    protected function notify_comment_author_about_approve(Model_ContentComment $comment)
    {
        // TODO Notify comment author
        $email = $comment->get_author_email();

        $data = [
            'name'  =>  $comment->get_author_name(),
        ];

        $subj = ''; // TODO

        $message = Notification::instance()
            ->message()
            ->set_subj($subj)
            ->set_template_name('moderator/comment/author-approve')
            ->set_template_data($data);

        if ($this->in_production()) {
            $to = NotificationUserEmail::factory($email);
            $message->set_to($to);
        } else {
            $message->to_current_user();
        }

        $message->send();
    }

    protected function notify_parent_comment_author_about_reply(Model_ContentComment $reply)
    {
        // Skip if parent comment email is equal to reply email

        // TODO Notify parent comment author
    }

    public function reject()
    {
        // Simply change status
        $this->do_transition(self::TRANSITION_REJECT);
    }

    public function spam()
    {
        // Simply change status
        $this->do_transition(self::TRANSITION_SPAM);
    }

    public function delete()
    {
        $this->do_transition(self::TRANSITION_DELETE);

        // TODO Delete child comments

        $this->model()->delete();
    }

    public function restore()
    {
        // Simply change status
        $this->do_transition(self::TRANSITION_RESTORE);
    }

    /**
     * @return \Model_ContentComment|\BetaKiller\Status\StatusRelatedModelInterface
     */
    protected function model()
    {
        return $this->model;
    }
}
