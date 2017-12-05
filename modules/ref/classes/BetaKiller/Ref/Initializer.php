<?php
namespace BetaKiller\Ref;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\EventHandlerInterface;
use BetaKiller\Repository\RefHitRepository;

class Initializer
{
    /**
     * @Inject
     * @var \BetaKiller\MessageBus\EventBus
     */
    private $eventBus;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefHitRepository
     */
    private $refHitsRepository;

    /**
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function init(): void
    {
        $this->registerEventsHandlers();
    }

    /**
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    private function registerEventsHandlers(): void
    {
        $this->eventBus->on(
            UrlDispatchedEvent::class,
            new class ($this->refHitsRepository) implements EventHandlerInterface
            {
                /**
                 * @var \BetaKiller\Repository\RefHitRepository
                 */
                private $refHitsRepository;

                /**
                 *  constructor.
                 *
                 * @param \BetaKiller\Repository\RefHitRepository $refLogRepository
                 */
                public function __construct(RefHitRepository $refLogRepository)
                {
                    $this->refHitsRepository = $refLogRepository;
                }

                /**
                 * @param \BetaKiller\Event\UrlDispatchedEvent $message
                 * @param \BetaKiller\MessageBus\EventBus      $bus
                 */
                public function handleEvent($message, EventBus $bus): void
                {
                    // Skip calls like "cache warmup" from CLI mode
                    if (PHP_SAPI === 'cli') {
                        return;
                    }

                    $model = $this->refHitsRepository->create();

                    $model
                        ->setSourceUrl($message->httpReferer)
                        ->setTargetUrl($message->url)
                        ->setIP($message->ip)
                        ->setTimestamp(new \DateTimeImmutable());

                    $this->refHitsRepository->save($model);
                }
            });
    }
}
