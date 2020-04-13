<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\Log\Logger;
use BetaKiller\Model\UserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class LoggerHelper
{
    public static function logException(
        LoggerInterface $logger,
        Throwable $e,
        UserInterface $user = null,
        ServerRequestInterface $request = null
    ): void {
        try {
            $data = [
                ':message'                    => $e->getMessage(),
                ':file'                       => $e->getFile(),
                ':line'                       => $e->getLine(),
                Logger::CONTEXT_KEY_EXCEPTION => $e,
            ];

            if ($user) {
                $data[Logger::CONTEXT_KEY_USER] = $user;
            }

            if ($request) {
                $data[Logger::CONTEXT_KEY_REQUEST] = $request;
            }

            $logger->alert(':message at :file::line', $data);
        } catch (Throwable $error) {
            /** @noinspection ForgottenDebugOutputInspection */
            \error_log(Exception::oneLiner($e));

            /** @noinspection ForgottenDebugOutputInspection */
            \error_log(Exception::oneLiner($error));
        }
    }
}