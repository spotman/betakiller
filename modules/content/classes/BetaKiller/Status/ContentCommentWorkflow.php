<?php
namespace BetaKiller\Status;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\ContentComment;
use BetaKiller\Model\UserInterface;
use Model_ContentCommentStatusTransition;

class ContentCommentWorkflow extends StatusWorkflow
{
    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * ContentCommentWorkflow constructor.
     *
     * @param \BetaKiller\Status\StatusRelatedModelInterface $model
     * @param \BetaKiller\Model\UserInterface                $user
     * @param \BetaKiller\Helper\NotificationHelper          $notificationHelper
     * @param \BetaKiller\Helper\IFaceHelper                 $ifaceHelper
     */
    public function __construct(
        StatusRelatedModelInterface $model,
        UserInterface $user,
        NotificationHelper $notificationHelper,
        IFaceHelper $ifaceHelper
    ) {
        parent::__construct($model, $user);

        $this->notificationHelper = $notificationHelper;
        $this->ifaceHelper        = $ifaceHelper;
    }

    /**
     * @throws \BetaKiller\Status\StatusWorkflowException
     */
    public function draft(): void
    {
        if ($this->model()->has_current_status()) {
            throw new StatusWorkflowException('Can not mark comment [:id] as draft coz it is in [:status] status', [
                ':id'     => $this->model()->getID(),
                ':status' => $this->model()->get_current_status()->get_codename(),
            ]);
        }

        $this->model()->set_start_status();
    }

    /**
     * @return \BetaKiller\Model\ContentComment|\BetaKiller\Status\StatusRelatedModelInterface
     */
    protected function model()
    {
        return $this->model;
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function approve(): void
    {
        $this->doTransition(Model_ContentCommentStatusTransition::APPROVE);

        $comment = $this->model();

        // Notify comment author
        $this->notify_comment_author_about_approve($comment);

        // Notify parent comment author
        $this->notify_parent_comment_author_about_reply($comment);
    }

    protected function notify_comment_author_about_approve(ContentComment $comment)
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
            'url'        => $comment->getPublicReadUrl($this->ifaceHelper),
            'created_at' => $comment->get_created_at()->format('H:i:s d.m.Y'),
            'label'      => $comment->getRelatedContentLabel(),
        ];

        $message = $this->notificationHelper
            ->createMessage('user/comment/author-approve')
            ->setTemplateData($data)
            ->addTargetEmail($email, $name);

        $this->notificationHelper->rewriteTargetsForDebug($message);

        $message->send();
    }

    /**
     * @param \BetaKiller\Model\ContentComment $reply
     */
    protected function notify_parent_comment_author_about_reply(ContentComment $reply)
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
            'url'        => $reply->getPublicReadUrl($this->ifaceHelper),
            'created_at' => $reply->get_created_at()->format('H:i:s d.m.Y'),
            'label'      => $reply->getRelatedContentLabel(),
        ];

        $message = $this->notificationHelper
            ->createMessage('user/comment/parent-author-reply')
            ->setTemplateData($data)
            ->addTargetEmail($parentEmail, $parentName);

        $this->notificationHelper->rewriteTargetsForDebug($message);

        $message->send();
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function reject(): void
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::REJECT);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function markAsSpam(): void
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::MARK_AS_SPAM);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function moveToTrash(): void
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::MOVE_TO_TRASH);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function restoreFromTrash(): void
    {
        // Simply change status
        $this->doTransition(Model_ContentCommentStatusTransition::RESTORE_FROM_TRASH);
    }
}
