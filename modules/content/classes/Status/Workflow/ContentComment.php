<?php

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Status\StatusWorkflow;
use BetaKiller\Status\StatusWorkflowException;

class Status_Workflow_ContentComment extends StatusWorkflow
{
    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * Status_Workflow_ContentComment constructor.
     *
     * @param \BetaKiller\Status\StatusRelatedModelInterface $model
     * @param \BetaKiller\Helper\NotificationHelper          $notificationHelper
     */
    public function __construct(StatusRelatedModelInterface $model, NotificationHelper $notificationHelper)
    {
        parent::__construct($model);

        $this->notificationHelper = $notificationHelper;
    }

    public function draft()
    {
        if ($this->model()->has_current_status()) {
            throw new StatusWorkflowException('Can not mark comment [:id] as draft coz it is in [:status] status', [
                ':id'     => $this->model()->get_id(),
                ':status' => $this->model()->get_current_status()->get_codename(),
            ]);
        }

        $this->model()->set_start_status();
    }

    /**
     * @return \Model_ContentComment|\BetaKiller\Status\StatusRelatedModelInterface
     */
    protected function model()
    {
        return $this->model;
    }

    public function approve()
    {
        $this->doTransition(Model_ContentCommentStatusTransition::APPROVE);

        $comment = $this->model();

        // Notify comment author
        $this->notify_comment_author_about_approve($comment);

        // Notify parent comment author
        $this->notify_parent_comment_author_about_reply($comment);
    }

    protected function notify_comment_author_about_approve(Model_ContentComment $comment)
    {
        $authorUser = $comment->get_author_user();

        // Skip notification for moderators
        if ($authorUser && $authorUser->isModerator()) {
            return;
        }

        $email = $comment->get_author_email();
        $name  = $comment->get_author_name();

        $data = [
            'name'       => $name,
            'url'        => $comment->get_public_url(),
            'created_at' => $comment->get_created_at()->format('H:i:s d.m.Y'),
            'label'      => $comment->get_related_content_label(),
        ];

        $message = $this->notificationHelper
            ->createMessage('user/comment/author-approve')
            ->setTemplateData($data)
            ->addTargetEmail($email, $name);

        $this->notificationHelper->rewriteTargetsForDebug($message);

        $message->send();
    }

    /**
     * @param \Model_ContentComment $reply
     */
    protected function notify_parent_comment_author_about_reply(Model_ContentComment $reply)
    {
        $parent = $reply->getParent();

        // Skip if comment has no parent
        if (!$parent) {
            return;
        }

        $replyEmail  = $reply->get_author_email();
        $parentEmail = $parent->get_author_email();

        // Skip if parent comment email is equal to reply email
        if ($replyEmail === $parentEmail) {
            return;
        }

        $parentName = $parent->get_author_name();

        $data = [
            'url'        => $reply->get_public_url(),
            'created_at' => $reply->get_created_at()->format('H:i:s d.m.Y'),
            'label'      => $reply->get_related_content_label(),
        ];

        $message = $this->notificationHelper
            ->createMessage('user/comment/parent-author-reply')
            ->setTemplateData($data)
            ->addTargetEmail($parentEmail, $parentName);

        $this->notificationHelper->rewriteTargetsForDebug($message);

        $message->send();
    }

    public function reject()
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::REJECT);
    }

    public function markAsSpam()
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::MARK_AS_SPAM);
    }

    public function moveToTrash()
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::MOVE_TO_TRASH);
    }

    public function restoreFromTrash()
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::RESTORE_FROM_TRASH);
    }
}
