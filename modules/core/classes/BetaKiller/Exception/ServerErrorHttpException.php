<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class ServerErrorHttpException extends HttpException
{
    public function __construct(string $message = null, array $variables = null)
    {
        parent::__construct(500, $message, $variables);
    }
}
