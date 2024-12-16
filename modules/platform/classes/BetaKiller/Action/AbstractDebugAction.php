<?php

declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\User;
use BetaKiller\Session\SessionStorageInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractDebugAction extends AbstractAction
{
    /**
     * AbstractDebugAction constructor.
     *
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        private SessionStorageInterface $sessionStorage,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = ServerRequestHelper::getUser($request);

        if (!$user->isDeveloper()) {
            throw new AccessDeniedException('Unauthorized debug mode');
        }

        $forUser = ServerRequestHelper::getEntity($request, User::class);

        $forSessions = $this->sessionStorage->getUserSessions($forUser ?? $user);

        foreach ($forSessions as $forSession) {
            $this->updateState($forSession);
        }

        return $forUser
            ? ResponseHelper::text(sprintf('Debug mode changed for User %s', $forUser->getID()))
            : ResponseHelper::redirect('/');
    }

    abstract protected function updateState(SessionInterface $session): void;
}
