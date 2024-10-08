<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class NotImplementedHttpException extends HttpException
{
    public function __construct(string $message = null, array $values = null)
    {
        parent::__construct(501, $message, $values);
    }
}
