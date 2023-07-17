<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\Model\UserInterface;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class LoggerHelper
{
    public const CONTEXT_KEY_USER      = 'user';
    public const CONTEXT_KEY_EXCEPTION = 'exception';
    public const CONTEXT_KEY_REQUEST   = 'request';

    public static function logUserException(
        LoggerInterface $logger,
        Throwable       $e,
        UserInterface   $user
    ): void {
        self::addRecord($logger, $e, $user);
    }

    public static function logRequestException(
        LoggerInterface        $logger,
        Throwable              $e,
        ServerRequestInterface $request
    ): void {
        self::addRecord($logger, $e, null, $request);
    }

    public static function logRawException(
        LoggerInterface $logger,
        Throwable       $e
    ): void {
        self::addRecord($logger, $e);
    }

    public static function logError(LoggerInterface $logger, string $message, array $subst = null): void
    {
        if ($subst) {
            $message = strtr($message, array_filter($subst, 'is_scalar'));
        }

        self::logRawException($logger, new LogicException($message));
    }

    private static function addRecord(
        LoggerInterface        $logger,
        Throwable              $e,
        UserInterface          $user = null,
        ServerRequestInterface $request = null
    ): void {
        try {
            $data = [
                ':message'                  => $e->getMessage(),
                ':file'                     => $e->getFile(),
                ':line'                     => $e->getLine(),
                self::CONTEXT_KEY_EXCEPTION => $e,
            ];

            if ($user) {
                $data[self::CONTEXT_KEY_USER] = $user;
            }

            if ($request) {
                $data[self::CONTEXT_KEY_REQUEST] = $request;
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
