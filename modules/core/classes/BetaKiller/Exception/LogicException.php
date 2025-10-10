<?php

declare(strict_types=1);

namespace BetaKiller\Exception;

use BetaKiller\Exception;
use Throwable;

class LogicException extends Exception
{
    public function __construct(string $message = null, array $variables = null, Throwable $previous = null)
    {
        parent::__construct($message ?? '', $variables, 500, $previous);
    }
}
