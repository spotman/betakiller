<?php

declare(strict_types=1);

namespace BetaKiller\Task\Error;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationGatewayInterface;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Notification\Message\DeveloperErrorPhpExceptionMessage;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final readonly class Notify implements ConsoleTaskInterface
{
    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    private UrlHelperInterface $urlHelper;

    /**
     * Notify constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     * @param \BetaKiller\Helper\NotificationHelper         $notification
     * @param \BetaKiller\Factory\UrlHelperFactory          $urlHelperFactory
     * @param \Psr\Log\LoggerInterface                      $logger
     */
    public function __construct(
        private PhpExceptionRepositoryInterface $repository,
        private NotificationGatewayInterface $notification,
        private LoggerInterface $logger,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->urlHelper = $urlHelperFactory->create();
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
        // Notify developers
        $this->notification->sendBroadcast(DeveloperErrorPhpExceptionMessage::createFrom($model, $this->urlHelper));

        // Saving last notification timestamp
        $model->wasNotified(new DateTimeImmutable());

        $this->repository->save($model);
    }
}
