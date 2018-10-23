<?php
namespace BetaKiller\Ref;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\MessageBus\EventHandlerInterface;
use BetaKiller\Repository\RefHitRepository;

class RefUrlDispatchedEventHandler implements EventHandlerInterface
{
    /**
     * @var \BetaKiller\Repository\RefHitRepository
     */
    private $refHitsRepository;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * RefUrlDispatchedEventHandler constructor.
     *
     * @param \BetaKiller\Repository\RefHitRepository $refLogRepository
     * @param \BetaKiller\Helper\AppEnvInterface      $appEnv
     */
    public function __construct(RefHitRepository $refLogRepository, AppEnvInterface $appEnv)
    {
        $this->refHitsRepository = $refLogRepository;
        $this->appEnv            = $appEnv;
    }

    /**
     * @param \BetaKiller\Event\UrlDispatchedEvent     $message
     */
    public function handleEvent($message): void
    {
        // Skip calls like "cache warmup" from CLI mode
        if ($this->appEnv->isCli()) {
            return;
        }

        $params = $message->getUrlContainer();

        // Fetch UTM tags if exists so IFace would not warn about unused parameters
        $params->getQueryPart('utm_source');
        $params->getQueryPart('utm_medium');
        $params->getQueryPart('utm_campaign');

        $model = $this->refHitsRepository->create();

        $model
            ->setSourceUrl($message->getHttpReferer())
            ->setTargetUrl($message->getUrl())
            ->setIP($message->getIp())
            ->setTimestamp(new \DateTimeImmutable());

        $this->refHitsRepository->save($model);
    }
}
