<?php
namespace BetaKiller\HitStat;

use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Repository\HitPageRepositoryInterface;
use BetaKiller\Service\HitService;

final class HitStatMissingUrlEventHandler
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \BetaKiller\Repository\HitPageRepositoryInterface
     */
    private HitPageRepositoryInterface $pageRepo;

    /**
     * @var \BetaKiller\Service\HitService
     */
    private HitService $service;

    /**
     * HitStatMissingUrlEventHandler constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface                   $appEnv
     * @param \BetaKiller\Repository\HitPageRepositoryInterface $pageRepo
     * @param \BetaKiller\Service\HitService                    $service
     */
    public function __construct(
        AppEnvInterface $appEnv,
        HitPageRepositoryInterface $pageRepo,
        HitService $service
    ) {
        $this->appEnv   = $appEnv;
        $this->pageRepo = $pageRepo;
        $this->service  = $service;
    }

    public function __invoke(MissingUrlEvent $message): void
    {
        // Skip processing during cache warmup
        if ($this->appEnv->isInternalWebServer()) {
            return;
        }

        $targetUri = $message->getMissedUri();

        // Get target page from Request
        $target = $this->service->getPageByFullUrl($targetUri, true);

        if ($target->isMissing()) {
            // Nothing to do
            return;
        }

        // Mark target page as missing
        $target->markAsMissing();

        $redirectToUrl = $message->getRedirectToUrl();

        // Set redirect target if provided
        if ($redirectToUrl) {
            $redirect = $this->service->getPageRedirectByUrl($redirectToUrl);

            // Set target in missed url
            $target->setRedirect($redirect);
        }

        // Save target page
        $this->pageRepo->save($target);
    }
}
