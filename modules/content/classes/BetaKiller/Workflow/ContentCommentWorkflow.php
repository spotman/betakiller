<?php
namespace BetaKiller\Workflow;

use BetaKiller\Acl\Resource\ContentCommentResource;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\ContentComment;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\ContentCommentStateRepositoryInterface;
use Spotman\Acl\AclInterface;

class ContentCommentWorkflow
{
    public const NOTIFICATION_AUTHOR_APPROVE = 'email/user/comment/author-approve';
    public const NOTIFICATION_PARENT_REPLY   = 'email/user/comment/parent-author-reply';

    public const APPROVE            = 'approve';
    public const REJECT             = 'reject';
    public const MARK_AS_SPAM       = 'markAsSpam';
    public const MOVE_TO_TRASH      = 'moveToTrash';
    public const RESTORE_FROM_TRASH = 'restoreFromTrash';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\Workflow\StatusWorkflowInterface
     */
    private $status;

    /**
     * @var \BetaKiller\Repository\ContentCommentStateRepository
     */
    private $stateRepo;

    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * ContentCommentWorkflow constructor.
     *
     * @param \BetaKiller\Helper\NotificationHelper                         $notificationHelper
     * @param \BetaKiller\Factory\UrlHelperFactory                          $urlHelperFactory
     * @param \BetaKiller\Workflow\StatusWorkflowInterface                  $statusWorkflow
     * @param \BetaKiller\Repository\ContentCommentStateRepositoryInterface $stateRepo
     * @param \Spotman\Acl\AclInterface                                     $acl
     */
    public function __construct(
        NotificationHelper $notificationHelper,
        UrlHelperFactory $urlHelperFactory,
        StatusWorkflowInterface $statusWorkflow,
        ContentCommentStateRepositoryInterface $stateRepo,
        AclInterface $acl
    ) {
        $this->notification = $notificationHelper;
        $this->urlHelper    = $urlHelperFactory->create();
        $this->status       = $statusWorkflow;
        $this->stateRepo    = $stateRepo;
        $this->acl          = $acl;
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     *
     * @throws \BetaKiller\Workflow\WorkflowException
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function draft(ContentCommentInterface $comment): void
    {
        $this->status->setStartState($comment);
    }

    public function initAsPending(ContentCommentInterface $comment): void
    {
        $status = $this->stateRepo->getPendingStatus();

        $comment->initWorkflowState($status);
    }

    public function initAsApproved(ContentCommentInterface $comment): void
    {
        $status = $this->stateRepo->getApprovedStatus();

        $comment->initWorkflowState($status);
    }

    public function initAsSpam(ContentCommentInterface $comment): void
    {
        $status = $this->stateRepo->getSpamStatus();

        $comment->initWorkflowState($status);
    }

    public function initAsTrash(ContentCommentInterface $comment): void
    {
        $status = $this->stateRepo->getTrashStatus();

        $comment->initWorkflowState($status);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Workflow\WorkflowException
     */
    public function approve(ContentCommentInterface $comment, UserInterface $user): void
    {
        $this->status->doTransition($comment, self::APPROVE, $user);

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
        if ($authorUser) {
            $resource = $this->acl->getResource(ContentComment::getModelName());

            $skipNotify = $this->acl->isAllowedToUser(
                $resource,
                ContentCommentResource::FLAG_SKIP_NOTIFY_AUTHOR_APPROVE,
                $authorUser
            );

            if ($skipNotify) {
                return;
            }
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
     * @throws \BetaKiller\Workflow\WorkflowException
     */
    public function reject(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::REJECT, $user);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Workflow\WorkflowException
     */
    public function markAsSpam(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::MARK_AS_SPAM, $user);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Workflow\WorkflowException
     */
    public function moveToTrash(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::MOVE_TO_TRASH, $user);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $comment
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \BetaKiller\Workflow\WorkflowException
     */
    public function restoreFromTrash(ContentCommentInterface $comment, UserInterface $user): void
    {
        // Simply change status
        $this->status->doTransition($comment, self::RESTORE_FROM_TRASH, $user);
    }
}
