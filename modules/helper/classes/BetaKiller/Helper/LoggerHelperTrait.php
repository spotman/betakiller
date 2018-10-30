<?php
namespace BetaKiller\Helper;

use BetaKiller\Log\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

trait LoggerHelperTrait
{
    final protected function logException(
        LoggerInterface $logger,
        Throwable $e,
        ServerRequestInterface $request = null
    ): void {
        $data = [
            ':message'                    => $e->getMessage(),
            ':file'                       => $e->getFile(),
            ':line'                       => $e->getLine(),
            Logger::CONTEXT_KEY_EXCEPTION => $e,
        ];

        if ($request) {
            $data[Logger::CONTEXT_KEY_REQUEST] = $request;
        }

        $logger->alert(':message at :file::line', $data);
    }
}
