<?php

declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractDebugAction extends AbstractAction
{
    /**
     * AbstractDebugAction constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
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

        $session = ServerRequestHelper::getSession($request);

        $this->updateState($session);

        return ResponseHelper::text(sprintf('Debug mode changed for User %s', $user->getID()));
    }

    abstract protected function updateState(SessionInterface $session): void;
}
