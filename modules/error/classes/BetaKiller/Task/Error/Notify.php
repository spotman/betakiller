<?php
declare(strict_types=1);

namespace BetaKiller\Task\Error;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\ZoneInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class Notify extends AbstractTask
{
    public const NOTIFICATION_PHP_EXCEPTION = 'developer/error/php-exception';

    /**
     * @var \BetaKiller\Repository\PhpExceptionRepositoryInterface
     */
    private $repository;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    private $urlHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Notify constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     * @param \BetaKiller\Helper\NotificationHelper         $notificationHelper
     * @param \BetaKiller\Factory\UrlHelperFactory          $urlHelperFactory
     * @param \Psr\Log\LoggerInterface                      $logger
     */
    public function __construct(
        PhpExceptionRepositoryInterface $repository,
        NotificationHelper $notificationHelper,
        UrlHelperFactory $urlHelperFactory,
        LoggerInterface $logger
    ) {
        $this->urlHelper    = $urlHelperFactory->create();
        $this->repository   = $repository;
        $this->notification = $notificationHelper;
        $this->logger       = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No cli arguments
        return [];
    }

    /**
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\ValidationException
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
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function notifyAboutException(PhpExceptionModelInterface $model): void
    {
        $target = $this->notification->debugEmailTarget('Bug Hunters');

        // Notify developers
        $this->notification->directMessage(self::NOTIFICATION_PHP_EXCEPTION, $target, [
            'message'  => $model->getMessage(),
            'urls'     => $model->getUrls(),
            'paths'    => $model->getPaths(),
            'adminUrl' => $this->urlHelper->getReadEntityUrl($model, ZoneInterface::ADMIN),
        ]);

        // Saving last notification timestamp
        $model->wasNotified(new DateTimeImmutable);

        $this->repository->save($model);
    }
}
