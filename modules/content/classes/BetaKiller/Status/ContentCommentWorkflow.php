<?php
namespace BetaKiller\Status;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentCommentStatusTransition;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\UserService;

class ContentCommentWorkflow extends StatusWorkflow
{
    public const NOTIFICATION_AUTHOR_APPROVE = 'user/comment/author-approve';
    public const NOTIFICATION_PARENT_REPLY   = 'user/comment/parent-author-reply';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * ContentCommentWorkflow constructor.
     *
     * @param \BetaKiller\Status\StatusRelatedModelInterface $model
     * @param \BetaKiller\Model\UserInterface                $user
     * @param \BetaKiller\Helper\NotificationHelper          $notificationHelper
     * @param \BetaKiller\Helper\UrlHelper                   $urlHelper
     * @param \BetaKiller\Service\UserService                $service
     */
    public function __construct(
        StatusRelatedModelInterface $model,
        UserInterface $user,
        NotificationHelper $notificationHelper,
        UrlHelper $urlHelper,
        UserService $service
    ) {
        parent::__construct($model, $user);

        $this->notification = $notificationHelper;
        $this->urlHelper    = $urlHelper;
        $this->userService  = $service;
    }

    /**
     * @throws \BetaKiller\Status\StatusWorkflowException
     */
    public function draft(): void
    {
        if ($this->model()->hasCurrentStatus()) {
            throw new StatusWorkflowException('Can not mark comment [:id] as draft coz it is in [:status] status', [
                ':id'     => $this->model()->getID(),
                ':status' => $this->model()->getCurrentStatus()->getCodename(),
            ]);
        }

        $this->model()->getStartStatus();
    }

    /**
     * @return \BetaKiller\Model\ContentComment|\BetaKiller\Status\StatusRelatedModelInterface
     */
    protected function model()
    {
        return $this->model;
    }

    /**
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Status\StatusException
     */
    public function approve(): void
    {
        $this->doTransition(ContentCommentStatusTransition::APPROVE);

        $comment = $this->model();

        // Notify comment author
        $this->notifyCommentAuthorAboutApprove($comment);

        // Notify parent comment author
        $this->notifyParentCommentAuthorAboutReply($comment);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     *
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function notifyCommentAuthorAboutApprove(ContentCommentInterface $comment): void
    {
        $authorUser = $comment->getAuthorUser();

        // Skip notification for moderators
        if ($authorUser && $this->userService->isModerator($authorUser)) {
            return;
        }

        $email = $comment->getAuthorEmail();
        $name  = $comment->getAuthorName();

        $target = $authorUser ?: $this->notification->emailTarget($email, $name);

        $this->notification->directMessage(self::NOTIFICATION_AUTHOR_APPROVE, $target, [
            'name'       => $name,
            'url'        => $comment->getPublicReadUrl($this->urlHelper),
            'created_at' => $comment->getCreatedAt()->format('H:i:s d.m.Y'),
            'label'      => $comment->getRelatedContentLabel(),
        ]);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $reply
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    protected function notifyParentCommentAuthorAboutReply(ContentCommentInterface $reply): void
    {
        $parent = $reply->getParent();

        // Skip if comment has no parent
        if (!$parent) {
            return;
        }

        $replyEmail  = $reply->getAuthorEmail();
        $parentEmail = $parent->getAuthorEmail();

        // Skip if parent comment email is equal to reply email
        if ($replyEmail === $parentEmail) {
            return;
        }

        $parentAuthor = $parent->getAuthorUser();

        $target = $parentAuthor ?: $this->notification->emailTarget($parentEmail, $parent->getAuthorName());

        $this->notification->directMessage(self::NOTIFICATION_PARENT_REPLY, $target, [
            'url'        => $reply->getPublicReadUrl($this->urlHelper),
            'created_at' => $reply->getCreatedAt()->format('H:i:s d.m.Y'),
            'label'      => $reply->getRelatedContentLabel(),
        ]);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     */
    public function reject(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::REJECT);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     */
    public function markAsSpam(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::MARK_AS_SPAM);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     */
    public function moveToTrash(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::MOVE_TO_TRASH);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     */
    public function restoreFromTrash(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::RESTORE_FROM_TRASH);
    }
}
