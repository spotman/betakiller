<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class BadRequestHttpException extends HttpException
{
    public function __construct(string $message = null, array $variables = null)
    {
        parent::__construct(400, $message, $variables);
    }
}
