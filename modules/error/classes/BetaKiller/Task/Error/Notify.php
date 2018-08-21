<?php
declare(strict_types=1);

namespace BetaKiller\Task\Error;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\ZoneInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class Notify extends AbstractTask
{
    /**
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repository;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Notify constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     * @param \BetaKiller\Helper\NotificationHelper         $notificationHelper
     * @param \BetaKiller\Helper\IFaceHelper                $ifaceHelper
     * @param \Psr\Log\LoggerInterface                      $logger
     */
    public function __construct(
        PhpExceptionRepository $repository,
        NotificationHelper $notificationHelper,
        IFaceHelper $ifaceHelper,
        LoggerInterface $logger
    ) {
        $this->repository         = $repository;
        $this->notificationHelper = $notificationHelper;
        $this->ifaceHelper        = $ifaceHelper;
        $this->logger             = $logger;

        parent::__construct();
    }

    /**
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    public function run(): void
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
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function notifyAboutException(PhpExceptionModelInterface $model): void
    {
        // Notify developers if needed
        $data = [
            'message'  => $model->getMessage(),
            'urls'     => $model->getUrls(),
            'paths'    => $model->getPaths(),
            'adminUrl' => $this->ifaceHelper->getReadEntityUrl($model, ZoneInterface::ADMIN),
        ];

        $message = $this->notificationHelper
            ->createMessage('developer/error/php-exception')
            ->setTemplateData($data);

        $this->notificationHelper
            ->toDevelopers($message)
            ->send($message);

        // Saving last notification timestamp
        $model->setLastNotifiedAt(new DateTimeImmutable);
        $model->wasNotified();

        $this->repository->save($model);
    }
}
