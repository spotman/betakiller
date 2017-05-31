<?php

use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Status\StatusWorkflow;
use BetaKiller\Status\StatusWorkflowException;

class Status_Workflow_ContentPost extends StatusWorkflow
{
    const TRANSITION_COMPLETE = 'complete';
    const TRANSITION_PUBLISH  = 'publish';
    const TRANSITION_PAUSE    = 'pause';
    const TRANSITION_FIX      = 'fix';

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @Inject
     * TODO move to constructor
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * Status_Workflow_ContentPost constructor.
     *
     * @param \BetaKiller\Status\StatusRelatedModelInterface      $model
     * @param \BetaKiller\Helper\NotificationHelper $notificationHelper
     */
    public function __construct(StatusRelatedModelInterface $model, NotificationHelper $notificationHelper)
    {
        parent::__construct($model);
        $this->notificationHelper = $notificationHelper;
    }

    public function draft()
    {
        if ($this->model()->has_current_status()) {
            throw new StatusWorkflowException('Can not mark post [:id] as draft coz it is in [:status] status', [
                ':id'     => $this->model()->get_id(),
                ':status' => $this->model()->get_current_status()->get_codename(),
            ]);
        }

        $this->model()->set_start_status();
    }

    public function complete()
    {
        $this->doTransition(self::TRANSITION_COMPLETE);

        // Publish post if it is allowed
        if ($this->isTransitionAllowed(self::TRANSITION_PUBLISH)) {
            $this->publish();
        } else {
            $this->notifyModeratorAboutCompletePost();
        }
    }

    private function notifyModeratorAboutCompletePost()
    {
        $message = $this->notificationHelper->createMessage('moderator/post/complete');

        $model = $this->model();

        $data = [
            'url'   => $this->ifaceHelper->getReadEntityUrl($model),
            'label' => $model->getLabel(),
        ];

        $message->setTemplateData($data);
        $this->notificationHelper->toModerators($message);

        $message->send();
    }

    public function publish()
    {
        $this->doTransition(self::TRANSITION_PUBLISH);

        $this->makeUri();

        // TODO Check for title/description/image and other critical elements before publishing

        // Publish latest revision
        $this->model()->setLatestRevisionAsActual();
    }

    public function pause()
    {
        $this->doTransition(self::TRANSITION_PAUSE);
    }

    public function fix()
    {
        $this->doTransition(self::TRANSITION_FIX);

        $this->notifyEditorAboutFixRequest();
    }

    private function notifyEditorAboutFixRequest()
    {
        // TODO Get user which is requested for fix
        // TODO Request content manager for editing
    }

    protected function makeUri()
    {
        // Nothing to do if uri was already set
        if ($this->model()->getUri()) {
            return;
        }

        $label = $this->model()->getLabel();

        if (!$label) {
            throw new StatusWorkflowException('Post [:id] must have uri or label before publishing', [
                ':id' => $this->model()->get_id(),
            ]);
        }

        $uri = URL::transliterate($label);

        // Saving uri
        $this->model()->setUri($uri);
    }

    /**
     * @return \Model_ContentPost|\BetaKiller\Status\StatusRelatedModelInterface
     */
    protected function model()
    {
        return $this->model;
    }
}
