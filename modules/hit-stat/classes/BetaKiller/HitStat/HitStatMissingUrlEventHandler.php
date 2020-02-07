<?php
namespace BetaKiller\HitStat;

use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Repository\HitPageRepository;
use BetaKiller\Service\HitService;

class HitStatMissingUrlEventHandler
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Repository\HitPageRepository
     */
    private $pageRepo;

    /**
     * @var \BetaKiller\Service\HitService
     */
    private $service;

    /**
     * HitStatMissingUrlEventHandler constructor.
     *
     * @param AppEnvInterface                          $appEnv
     * @param \BetaKiller\Repository\HitPageRepository $pageRepo
     * @param \BetaKiller\Service\HitService           $service
     */
    public function __construct(
        AppEnvInterface $appEnv,
        HitPageRepository $pageRepo,
        HitService $service
    ) {
        $this->appEnv   = $appEnv;
        $this->pageRepo = $pageRepo;
        $this->service  = $service;
    }

    public function __invoke(MissingUrlEvent $message): void
    {
        // Skip calls like "cache warmup" from CLI mode
        if ($this->appEnv->isInternalWebServer()) {
            return;
        }

        $request = $message->getRequest();

        if (HitStatRequestHelper::hasHit($request)) {
            // Get target page from Request
            $target = HitStatRequestHelper::getHit($request)->getTargetPage();

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
}
