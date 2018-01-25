<?php
namespace BetaKiller\Status;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentCommentStatusTransition;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\UserService;

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
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * ContentCommentWorkflow constructor.
     *
     * @param \BetaKiller\Status\StatusRelatedModelInterface $model
     * @param \BetaKiller\Model\UserInterface                $user
     * @param \BetaKiller\Helper\NotificationHelper          $notificationHelper
     * @param \BetaKiller\Helper\IFaceHelper                 $ifaceHelper
     * @param \BetaKiller\Service\UserService                $service
     */
    public function __construct(
        StatusRelatedModelInterface $model,
        UserInterface $user,
        NotificationHelper $notificationHelper,
        IFaceHelper $ifaceHelper,
        UserService $service
    ) {
        parent::__construct($model, $user);

        $this->notificationHelper = $notificationHelper;
        $this->ifaceHelper        = $ifaceHelper;
        $this->userService        = $service;
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
     * @throws \HTTP_Exception_501
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
    protected function notifyCommentAuthorAboutApprove(ContentCommentInterface $comment)
    {
        $authorUser = $comment->getAuthorUser();

        // Skip notification for moderators
        if ($authorUser && $this->userService->isModerator($authorUser)) {
            return;
        }

        $email = $comment->getAuthorEmail();
        $name  = $comment->getAuthorName();

        $data = [
            'name'       => $name,
            'url'        => $comment->getPublicReadUrl($this->ifaceHelper),
            'created_at' => $comment->getCreatedAt()->format('H:i:s d.m.Y'),
            'label'      => $comment->getRelatedContentLabel(),
        ];

        $message = $this->notificationHelper
            ->createMessage('user/comment/author-approve')
            ->setTemplateData($data)
            ->addTargetEmail($email, $name);

        $this->notificationHelper
            ->rewriteTargetsForDebug($message)
            ->send($message);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentInterface $reply
     *
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function notifyParentCommentAuthorAboutReply(ContentCommentInterface $reply)
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

        $parentName = $parent->getAuthorName();

        $data = [
            'url'        => $reply->getPublicReadUrl($this->ifaceHelper),
            'created_at' => $reply->getCreatedAt()->format('H:i:s d.m.Y'),
            'label'      => $reply->getRelatedContentLabel(),
        ];

        $message = $this->notificationHelper
            ->createMessage('user/comment/parent-author-reply')
            ->setTemplateData($data)
            ->addTargetEmail($parentEmail, $parentName);

        $this->notificationHelper
            ->rewriteTargetsForDebug($message)
            ->send($message);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function reject(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::REJECT);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function markAsSpam(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::MARK_AS_SPAM);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function moveToTrash(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::MOVE_TO_TRASH);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function restoreFromTrash(): void
    {
        // Simply change status
        $this->doTransition(ContentCommentStatusTransition::RESTORE_FROM_TRASH);
    }
}
