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
        $context = $record->context;

        if (isset($context[LoggerHelper::CONTEXT_KEY_EXCEPTION])) {
            unset($context[LoggerHelper::CONTEXT_KEY_EXCEPTION]);
        }

        if (isset($context[LoggerHelper::CONTEXT_KEY_REQUEST])) {
            $request = $context[LoggerHelper::CONTEXT_KEY_REQUEST];

            assert($request instanceof ServerRequestInterface);

            $id = ServerRequestHelper::getRequestUuid($request);

            if ($id) {
                $context[LoggerHelper::CONTEXT_KEY_REQUEST] = $id;
            } else {
                unset($context[LoggerHelper::CONTEXT_KEY_REQUEST]);
            }
        }

        if (isset($context[LoggerHelper::CONTEXT_KEY_USER])) {
            $user = $context[LoggerHelper::CONTEXT_KEY_USER];

            assert($user instanceof UserInterface);

            $context[LoggerHelper::CONTEXT_KEY_USER] = $user->getID();
        }

        return $record->with(context: $context);
    }
}
