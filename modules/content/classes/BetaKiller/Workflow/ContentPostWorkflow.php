<?php
namespace BetaKiller\Workflow;

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\ZoneInterface;
use URL;

class ContentPostWorkflow
{
    public const COMPLETE         = 'complete';
    public const PUBLISH = 'publish';
    public const PAUSE   = 'pause';
    public const FIX     = 'fix';

    public const NOTIFICATION_POST_COMPLETE = 'moderator/post/complete';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\Workflow\StatusWorkflow
     */
    private $status;

    /**
     * ContentPostWorkflow constructor.
     *
     * @param \BetaKiller\Workflow\StatusWorkflowInterface   $workflow
     * @param \BetaKiller\Helper\NotificationHelper $notificationHelper
     * @param \BetaKiller\Helper\UrlHelper          $urlHelper
     */
    public function __construct(
        StatusWorkflowInterface $workflow,
        NotificationHelper $notificationHelper,
        UrlHelper $urlHelper
    ) {
        $this->notification = $notificationHelper;
        $this->urlHelper    = $urlHelper;
        $this->status       = $workflow;
    }

    /**
     * @param \BetaKiller\Model\ContentPostInterface $post
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function draft(ContentPostInterface $post): void
    {
        $this->status->setStartState($post);
    }

    /**
     * @param \BetaKiller\Model\ContentPostInterface $post
     * @param \BetaKiller\Model\UserInterface        $user
     *
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Workflow\WorkflowException
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function complete(ContentPostInterface $post, UserInterface $user): void
    {
        $this->status->doTransition($post, self::COMPLETE, $user);

        // Publish post if it is allowed
        if ($this->status->isTransitionAllowed($post, self::PUBLISH, $user)) {
            $this->publish($post, $user);
        } else {
            $this->notifyModeratorAboutCompletePost($post);
        }
    }

    /**
     * @param \BetaKiller\Model\ContentPostInterface $post
     *
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function notifyModeratorAboutCompletePost(ContentPostInterface $post): void
    {
        $this->notification->broadcastMessage(self::NOTIFICATION_POST_COMPLETE, [
            'url'   => $this->urlHelper->getReadEntityUrl($post, ZoneInterface::ADMIN),
            'label' => $post->getLabel(),
        ]);
    }

    /**
     * @param \BetaKiller\Model\ContentPostInterface $post
     * @param \BetaKiller\Model\UserInterface        $user
     *
     * @throws \BetaKiller\Workflow\WorkflowException
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function publish(ContentPostInterface $post, UserInterface $user): void
    {
        $this->status->doTransition($post, self::PUBLISH, $user);

        $this->makeUri($post);

        // TODO Check for title/description/image and other critical elements before publishing

        // Publish latest revision
        $post->setLatestRevisionAsActual();
    }

    /**
     * @param \BetaKiller\Model\ContentPostInterface $post
     * @param \BetaKiller\Model\UserInterface        $user
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function pause(ContentPostInterface $post, UserInterface $user): void
    {
        $this->status->doTransition($post, self::PAUSE, $user);
    }

    /**
     * @param \BetaKiller\Model\ContentPostInterface $post
     * @param \BetaKiller\Model\UserInterface        $user
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function fix(ContentPostInterface $post, UserInterface $user): void
    {
        $this->status->doTransition($post, self::FIX, $user);

        $this->notifyEditorAboutFixRequest();
    }

    private function notifyEditorAboutFixRequest(): void
    {
        // TODO Get user which is requested for fix
        // TODO Request content manager for editing
    }

    /**
     * @param \BetaKiller\Model\ContentPostInterface $post
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    protected function makeUri(ContentPostInterface $post): void
    {
        // Nothing to do if uri was already set
        if ($post->getUri()) {
            return;
        }

        $label = $post->getLabel();

        if (!$label) {
            throw new WorkflowStateException('Post [:id] must have uri or label before publishing', [
                ':id' => $post->getID(),
            ]);
        }

        $uri = URL::transliterate($label);

        // Saving uri
        $post->setUri($uri);
    }
}
