<?php

namespace BetaKiller\Log;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserInterface;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContextCleanupProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
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
