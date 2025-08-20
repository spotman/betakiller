<?php

declare(strict_types=1);

namespace BetaKiller\Task\Error;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Notification\Message\DeveloperErrorPhpExceptionMessage;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class Notify extends AbstractTask
{
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
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        // No cli arguments
        return [];
    }

    /**
     * @param \BetaKiller\Console\ConsoleInputInterface $params *
     *
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\ValidationException
     */
    public function run(ConsoleInputInterface $params): void
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
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function notifyAboutException(PhpExceptionModelInterface $model): void
    {
        $target = $this->notification->debugEmailTarget('Bug Hunters');

        // Notify developers
        $this->notification->sendDirect($target, DeveloperErrorPhpExceptionMessage::createFrom($model, $this->urlHelper));

        // Saving last notification timestamp
        $model->wasNotified(new DateTimeImmutable());

        $this->repository->save($model);
    }
}
