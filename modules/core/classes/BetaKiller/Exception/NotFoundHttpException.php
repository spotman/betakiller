<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

use Throwable;

class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = null, array $values = null, Throwable $previous = null)
    {
        parent::__construct(404, $message, $values, $previous);
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
