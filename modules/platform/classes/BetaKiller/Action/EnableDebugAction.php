<?php

declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Helper\SessionHelper;
use Mezzio\Session\SessionInterface;

final readonly class EnableDebugAction extends AbstractDebugAction
{
    protected function updateState(SessionInterface $session): void
    {
        // Skip duplicate calls
        if (SessionHelper::isDebugEnabled($session)) {
            return;
        }

        SessionHelper::enableDebug($session);

        $this->logger->notice('Debug mode enabled for User :user_id with Session ":session_id"', [
            ':user_id'    => SessionHelper::getUserID($session),
            ':session_id' => SessionHelper::getId($session),
        ]);
    }
}
