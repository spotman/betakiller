<?php
namespace BetaKiller\Ref;

use BetaKiller\Helper\AppEnv;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\EventHandlerInterface;
use BetaKiller\Repository\RefHitRepository;

class RefUrlDispatchedEventHandler implements EventHandlerInterface
{
    /**
     * @var \BetaKiller\Repository\RefHitRepository
     */
    private $refHitsRepository;

    /**
     * @var AppEnv
     */
    private $appEnv;

    /**
     * RefUrlDispatchedEventHandler constructor.
     *
     * @param \BetaKiller\Repository\RefHitRepository $refLogRepository
     * @param \BetaKiller\Helper\AppEnv               $appEnv
     */
    public function __construct(RefHitRepository $refLogRepository, AppEnv $appEnv)
    {
        $this->refHitsRepository = $refLogRepository;
        $this->appEnv            = $appEnv;
    }

    /**
     * @param \BetaKiller\Event\UrlDispatchedEvent $message
     * @param \BetaKiller\MessageBus\EventBus      $bus
     */
    public function handleEvent($message, EventBus $bus): void
    {
        // Skip calls like "cache warmup" from CLI mode
        if ($this->appEnv->isCLI()) {
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
}
