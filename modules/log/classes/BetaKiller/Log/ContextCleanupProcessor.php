<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContextCleanupProcessor
{
    /**
     * @param string[][] $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        if (isset($record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION])) {
            unset($record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION]);
        }

        if (isset($record['context'][LoggerHelper::CONTEXT_KEY_REQUEST])) {
            $request = $record['context'][LoggerHelper::CONTEXT_KEY_REQUEST];

            assert($request instanceof ServerRequestInterface);

            $id = ServerRequestHelper::getRequestUuid($request);

            if ($id) {
                $record['context'][LoggerHelper::CONTEXT_KEY_REQUEST] = $id;
            } else {
                unset($record['context'][LoggerHelper::CONTEXT_KEY_REQUEST]);
            }
        }

        if (isset($record['context'][LoggerHelper::CONTEXT_KEY_USER])) {
            $user = $record['context'][LoggerHelper::CONTEXT_KEY_USER];

            assert($user instanceof UserInterface);

            $record['context'][LoggerHelper::CONTEXT_KEY_USER] = $user->getID();
        }

        return $record;
    }
}
