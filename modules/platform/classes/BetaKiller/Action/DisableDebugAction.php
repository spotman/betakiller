<?php

declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Helper\SessionHelper;
use Mezzio\Session\SessionInterface;

final readonly class DisableDebugAction extends AbstractDebugAction
{
    protected function updateState(SessionInterface $session): void
    {
        // Skip duplicate calls
        if (SessionHelper::hasDebugDefined($session) && !SessionHelper::isDebugEnabled($session)) {
            return;
        }

        SessionHelper::disableDebug($session);

        $this->logger->notice('Debug mode disabled for User :user_id with Session ":session_id"', [
            ':user_id'    => SessionHelper::getUserID($session),
            ':session_id' => SessionHelper::getId($session),
        ]);
    }
}
