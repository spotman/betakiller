<?php

use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Model\IFaceZone;

class Task_Error_Notify extends AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repository;

    /**
     * @Inject
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    protected function _execute(array $params)
    {
        // Repository returns filtered notifications
        $exceptions = $this->repository->getRequiredNotification();

        if (!\count($exceptions)) {
            $this->logger->debug('No one exception needs notification');
            return;
        }

        foreach ($exceptions as $exception) {
            $this->notifyAboutException($exception);
        }
    }

    /**
     * @param \BetaKiller\Model\PhpExceptionModelInterface $model
     */
    private function notifyAboutException(PhpExceptionModelInterface $model): void
    {
        // Notify developers if needed
        $data = [
            'message'  => $model->getMessage(),
            'urls'     => $model->getUrls(),
            'paths'    => $model->getPaths(),
            'adminUrl' => $this->ifaceHelper->getReadEntityUrl($model, IFaceZone::ADMIN_ZONE),
        ];

        $message = $this->notificationHelper->createMessage('developer/error/php-exception');

        $this->notificationHelper->toDevelopers($message);

        $message
            ->setSubj('BetaKiller exception')
            ->setTemplateData($data)
            ->send();

        // Saving last notification timestamp
        $model->setLastNotifiedAt(new DateTimeImmutable);
        $model->wasNotified();

        $this->repository->save($model);
    }
}
