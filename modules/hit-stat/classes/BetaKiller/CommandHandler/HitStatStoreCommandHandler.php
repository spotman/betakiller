<?php
declare(strict_types=1);

namespace BetaKiller\CommandHandler;

use BetaKiller\Command\HitStatStoreCommand;
use BetaKiller\Model\Hit;
use BetaKiller\Repository\HitRepository;

final class HitStatStoreCommandHandler
{
    /**
     * @var \BetaKiller\Repository\HitRepository
     */
    private $hitRepo;

    /**
     * HitStatStoreCommandHandler constructor.
     *
     * @param \BetaKiller\Repository\HitRepository $hitRepo
     */
    public function __construct(HitRepository $hitRepo)
    {
        $this->hitRepo = $hitRepo;
    }

    public function __invoke(HitStatStoreCommand $command): void
    {
        $source = $command->getSource();
        $target = $command->getTarget();
        $marker = $command->getMarker();
        $moment = $command->getMoment();
        $ip     = $command->getIp();
        $uuid   = $command->getUuid();
        $token  = $command->getSessionToken();

        // Create new Hit object with source/target pages, marker, ip and other info
        $hit = new Hit;

        // Store User/Session and hit UUID
        $hit
            ->setUuid($uuid)
            ->setSessionToken($token);

        $hit
            ->setTargetPage($target)
            ->setIP($ip)
            ->setTimestamp($moment);

        if ($source) {
            $hit->setSourcePage($source);
        }

        if ($marker) {
            $hit->setTargetMarker($marker);
        }

        $this->hitRepo->save($hit);
    }
}
