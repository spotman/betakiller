<?php
namespace BetaKiller\MissingUrl;

use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Helper\AppEnv;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\EventHandlerInterface;
use BetaKiller\Model\MissingUrlModelInterface;
use BetaKiller\Model\MissingUrlRedirectTargetModelInterface;
use BetaKiller\Model\MissingUrlReferrerModelInterface;
use BetaKiller\Repository\MissingUrlRedirectTargetRepository;
use BetaKiller\Repository\MissingUrlReferrerRepository;
use BetaKiller\Repository\MissingUrlRepository;

class MissingUrlEventHandler implements EventHandlerInterface
{
    /**
     * @var AppEnv
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Repository\MissingUrlRepository
     */
    private $missingUrlRepository;

    /**
     * @var \BetaKiller\Repository\MissingUrlRedirectTargetRepository
     */
    private $targetUrlRepository;

    /**
     * @var \BetaKiller\Repository\MissingUrlReferrerRepository
     */
    private $referrerRepository;

    /**
     * MissingUrlEventHandler constructor.
     *
     * @param AppEnv                                                    $appEnv
     * @param \BetaKiller\Repository\MissingUrlRepository               $missingUrlRepository
     * @param \BetaKiller\Repository\MissingUrlRedirectTargetRepository $targetUrlRepository
     * @param \BetaKiller\Repository\MissingUrlReferrerRepository       $referrerRepository
     */
    public function __construct(
        AppEnv $appEnv,
        MissingUrlRepository $missingUrlRepository,
        MissingUrlRedirectTargetRepository $targetUrlRepository,
        MissingUrlReferrerRepository $referrerRepository
    ) {
        $this->appEnv               = $appEnv;
        $this->missingUrlRepository = $missingUrlRepository;
        $this->targetUrlRepository  = $targetUrlRepository;
        $this->referrerRepository   = $referrerRepository;
    }

    /**
     * @param \BetaKiller\Event\MissingUrlEvent $message
     * @param \BetaKiller\MessageBus\EventBus   $bus
     *
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function handleEvent($message, EventBus $bus): void
    {
        // Skip calls like "cache warmup" from CLI mode
        if ($this->appEnv->isCLI()) {
            return;
        }

        $missingUrlModel = $this->getMissingUrlModel($message);

        $httpReferer = $message->getHttpReferer();
        $ipAddress = $message->getIpAddress();

        if ($httpReferer) {
            $referrerModel = $this->getReferrerModel($httpReferer, $ipAddress);

            if (!$missingUrlModel->hasReferrer($referrerModel)) {
                $missingUrlModel->addReferrer($referrerModel);

                // TODO Mark url for notification about new referrer (set $newReferrer flag to true)
            }
        }

        // TODO Implement statuses in missed urls
        // If status is "new"
            // TODO Notify moderators about new missed URL
        // Else if $newReferrer === true
            // TODO Notify moderators about new referrer in missing URL

        $missingUrlModel->setLastSeenAt(new \DateTimeImmutable);

        $this->missingUrlRepository->save($missingUrlModel);
    }

    /**
     * @param \BetaKiller\Event\MissingUrlEvent $event
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getMissingUrlModel(MissingUrlEvent $event): MissingUrlModelInterface
    {
        $missedUrl     = $event->getMissedUrl();
        $redirectToUrl = $event->getRedirectToUrl();
        $parentIFace   = $event->getParentModel();

        $missedUrlModel = $this->missingUrlRepository->findByUrl($missedUrl);

        if ($missedUrlModel) {
            return $missedUrlModel;
        }

        // Create new entity
        $missedUrlModel = $this->missingUrlRepository->create()->setMissedUrl($missedUrl);

        // Set redirect target if provided
        if ($redirectToUrl) {
            $redirectModel = $this->getRedirectTargetModel($redirectToUrl, $parentIFace);

            // Set target in missed url
            $missedUrlModel->setRedirectTarget($redirectModel);
        }

        // Store fresh model
        $this->missingUrlRepository->save($missedUrlModel);

        return $missedUrlModel;
    }

    /**
     * @param string                                     $redirectToUrl
     * @param \BetaKiller\IFace\IFaceModelInterface|null $parentIFace
     *
     * @return \BetaKiller\Model\MissingUrlRedirectTargetModelInterface
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    private function getRedirectTargetModel(string $redirectToUrl, ?IFaceModelInterface $parentIFace): MissingUrlRedirectTargetModelInterface
    {
        $redirectModel = $this->targetUrlRepository->findByUrl($redirectToUrl);

        if (!$redirectModel) {
            $redirectModel = $this->targetUrlRepository->create();

            if ($redirectToUrl) {
                $redirectModel->setUrl($redirectToUrl);
            }

            if ($parentIFace) {
                $redirectModel->setParentIFaceModel($parentIFace);
            }

            // Store fresh model
            $this->targetUrlRepository->save($redirectModel);
        }

        return $redirectModel;
    }

    /**
     * @param string $httpReferer
     *
     * @param string $ipAddress
     *
     * @return \BetaKiller\Model\MissingUrlReferrerModelInterface
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    private function getReferrerModel(string $httpReferer, string $ipAddress): MissingUrlReferrerModelInterface
    {
        $referrerModel = $this->referrerRepository->findByHttpReferer($httpReferer);

        if (!$referrerModel) {
            $referrerModel = $this->referrerRepository->create()
                ->setHttpReferer($httpReferer)
                ->setIpAddress($ipAddress);
        }

        $referrerModel->setLastSeenAt(new \DateTimeImmutable);

        $this->referrerRepository->save($referrerModel);

        return $referrerModel;
    }
}
