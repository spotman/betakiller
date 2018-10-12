<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class ServerErrorHttpException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct(500, $message);
    }
}
