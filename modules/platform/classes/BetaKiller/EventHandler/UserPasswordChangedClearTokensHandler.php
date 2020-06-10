<?php
declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\UserPasswordChangedEvent;
use BetaKiller\Repository\TokenRepositoryInterface;

final class UserPasswordChangedClearTokensHandler
{
    /**
     * @var \BetaKiller\Repository\TokenRepositoryInterface
     */
    private TokenRepositoryInterface $tokenRepo;

    /**
     * UserPasswordChangedClearTokensHandler constructor.
     *
     * @param \BetaKiller\Repository\TokenRepositoryInterface $tokenRepo
     */
    public function __construct(TokenRepositoryInterface $tokenRepo)
    {
        $this->tokenRepo = $tokenRepo;
    }

    public function __invoke(UserPasswordChangedEvent $event): void
    {
        $user = $event->getUser();

        // Disable all user tokens after password change to prevent attacks
        foreach ($this->tokenRepo->getUserTokens($user) as $item) {
            $item->setUsedAt(new \DateTimeImmutable('-10 seconds'));
            $this->tokenRepo->save($item);
        }
    }
}
