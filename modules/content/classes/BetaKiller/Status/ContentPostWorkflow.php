<?php
namespace BetaKiller\Status;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\UserInterface;
use URL;

class ContentPostWorkflow extends StatusWorkflow
{
    public const TRANSITION_COMPLETE = 'complete';
    public const TRANSITION_PUBLISH  = 'publish';
    public const TRANSITION_PAUSE    = 'pause';
    public const TRANSITION_FIX      = 'fix';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * ContentPostWorkflow constructor.
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
    public function draft()
    {
        if ($this->model()->hasCurrentStatus()) {
            throw new StatusWorkflowException('Can not mark post [:id] as draft coz it is in [:status] status', [
                ':id'     => $this->model()->getID(),
                ':status' => $this->model()->getCurrentStatus()->getCodename(),
            ]);
        }

        $this->model()->getStartStatus();
    }

    /**
     * @throws \Kohana_Exception
     * @throws \BetaKiller\Status\StatusWorkflowException
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function complete(): void
    {
        $this->doTransition(self::TRANSITION_COMPLETE);

        // Publish post if it is allowed
        if ($this->isTransitionAllowed(self::TRANSITION_PUBLISH)) {
            $this->publish();
        } else {
            $this->notifyModeratorAboutCompletePost();
        }
    }

    /**
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     */
    private function notifyModeratorAboutCompletePost()
    {
        $message = $this->notificationHelper->createMessage('moderator/post/complete');

        $model = $this->model();

        $data = [
            'url'   => $this->ifaceHelper->getReadEntityUrl($model),
            'label' => $model->getLabel(),
        ];

        $message->setTemplateData($data);
        $this->notificationHelper
            ->toModerators($message)
            ->send($message);
    }

    /**
     * @throws \Kohana_Exception
     * @throws \BetaKiller\Status\StatusException
     * @throws \BetaKiller\Status\StatusWorkflowException
     * @throws \HTTP_Exception_501
     */
    public function publish(): void
    {
        $this->doTransition(self::TRANSITION_PUBLISH);

        $this->makeUri();

        // TODO Check for title/description/image and other critical elements before publishing

        // Publish latest revision
        $this->model()->setLatestRevisionAsActual();
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function pause(): void
    {
        $this->doTransition(self::TRANSITION_PAUSE);
    }

    /**
     * @throws \BetaKiller\Status\StatusException
     * @throws \HTTP_Exception_501
     */
    public function fix(): void
    {
        $this->doTransition(self::TRANSITION_FIX);

        $this->notifyEditorAboutFixRequest();
    }

    private function notifyEditorAboutFixRequest()
    {
        // TODO Get user which is requested for fix
        // TODO Request content manager for editing
    }

    /**
     * @throws \BetaKiller\Status\StatusWorkflowException
     * @throws \Kohana_Exception
     */
    protected function makeUri(): void
    {
        // Nothing to do if uri was already set
        if ($this->model()->getUri()) {
            return;
        }

        $label = $this->model()->getLabel();

        if (!$label) {
            throw new StatusWorkflowException('Post [:id] must have uri or label before publishing', [
                ':id' => $this->model()->getID(),
            ]);
        }

        $uri = URL::transliterate($label);

        // Saving uri
        $this->model()->setUri($uri);
    }

    /**
     * @return \BetaKiller\Model\ContentPost|\BetaKiller\Status\StatusRelatedModelInterface
     */
    protected function model()
    {
        return $this->model;
    }
}
