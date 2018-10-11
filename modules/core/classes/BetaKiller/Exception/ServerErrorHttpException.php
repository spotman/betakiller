<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class ServerErrorHttpException extends HttpException
{
    public function __construct()
    {
        parent::__construct(500);
    }
}
