<?php
namespace BetaKiller\Workflow;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\UserService;

class ContentCommentWorkflow
{
    public const NOTIFICATION_AUTHOR_APPROVE = 'user/comment/author-approve';
    public const NOTIFICATION_PARENT_REPLY   = 'user/comment/parent-author-reply';

    public const TRANSITION_APPROVE            = 'approve';
    public const TRANSITION_REJECT             = 'reject';
    public const TRANSITION_MARK_AS_SPAM       = 'markAsSpam';
    public const TRANSITION_MOVE_TO_TRASH      = 'moveToTrash';
    public const TRANSITION_RESTORE_FROM_TRASH = 'restoreFromTrash';

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
     * @var \BetaKiller\Workflow\StatusWorkflowInterface
     */
    private $status;

    /**
     * ContentCommentWorkflow constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper        $notificationHelper
     * @param \BetaKiller\Helper\UrlHelper                 $urlHelper
     * @param \BetaKiller\Service\UserService              $service
     * @param \BetaKiller\Workflow\StatusWorkflowInterface $statusWorkflow
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        UrlHelper $urlHelper,
        UserService $service,
        StatusWorkflowInterface $statusWorkflow
    ) {
        $this->notification = $notificationHelper;
        $this->urlHelper    = $urlHelper;
        $this->userService  = $service;
        $this->status       = $statusWorkflow;
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     *
     * @throws \BetaKiller\Workflow\StatusException
     * @throws \BetaKiller\Workflow\StatusWorkflowException
     */
    public function draft(ContentCommentInterface $comment): void
    {
        $this->status->setStartState($comment);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Workflow\StatusException
     */
    public function approve(ContentCommentInterface $comment, UserInterface $user): void
    {
        $this->status->doTransition($comment, self::TRANSITION_APPROVE, $user);

        // Notify comment author
        $this->notifyCommentAuthorAboutApprove($comment);

        // Notify parent comment author
        $this->notifyParentCommentAuthorAboutReply($comment);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    protected function notifyCommentAuthorAboutApprove(ContentCommentInterface $comment): void
    {
        $authorUser = $comment->getAuthorUser();

        // Skip notification for moderators
        if ($authorUser && $this->userService->isAdmin($authorUser)) {
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
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Workflow\StatusException
     */
    public function reject(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::TRANSITION_REJECT, $user);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Workflow\StatusException
     */
    public function markAsSpam(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::TRANSITION_MARK_AS_SPAM, $user);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Workflow\StatusException
     */
    public function moveToTrash(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::TRANSITION_MOVE_TO_TRASH, $user);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Workflow\StatusException
     */
    public function restoreFromTrash(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::TRANSITION_RESTORE_FROM_TRASH, $user);
    }
}
