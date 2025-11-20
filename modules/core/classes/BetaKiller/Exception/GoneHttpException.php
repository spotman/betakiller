<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class GoneHttpException extends HttpException
{
    public function __construct(string $message = null, array $values = null)
    {
        parent::__construct(410, $message, $values);
    }

    public function isNotificationEnabled(): bool
    {
        return false;
    }

    public function alwaysShowNiceMessage(): bool
    {
        return true;
    }
}
