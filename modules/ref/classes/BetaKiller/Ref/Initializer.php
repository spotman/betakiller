<?php
namespace BetaKiller\Ref;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\EventHandlerInterface;
use BetaKiller\Repository\RefLogRepository;

class Initializer
{
    /**
     * @Inject
     * @var \BetaKiller\MessageBus\EventBus
     */
    private $eventBus;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefLogRepository
     */
    private $refLogRepository;

    public function init(): void
    {
        $this->registerEventsHandlers();
    }

    private function registerEventsHandlers(): void
    {
        $this->eventBus->on(
            UrlDispatchedEvent::class,
            new class ($this->refLogRepository) implements EventHandlerInterface
            {
                /**
                 * @var \BetaKiller\Repository\RefLogRepository
                 */
                private $refLogRepository;

                /**
                 *  constructor.
                 *
                 * @param \BetaKiller\Repository\RefLogRepository $refLogRepository
                 */
                public function __construct(RefLogRepository $refLogRepository)
                {
                    $this->refLogRepository = $refLogRepository;
                }

                /**
                 * @param \BetaKiller\Event\UrlDispatchedEvent $message
                 * @param \BetaKiller\MessageBus\EventBus      $bus
                 */
                public function handleEvent($message, EventBus $bus): void
                {
                    if (!$message->httpReferer) {
                        return;
                    }

                    $model = $this->refLogRepository->create();

                    $model
                        ->setSourceUrl($message->httpReferer)
                        ->setTargetUrl($message->url)
                        ->setIP($message->ip)
                        ->setTimestamp(new \DateTimeImmutable());

                    $this->refLogRepository->save($model);
                }
            });
    }
}
