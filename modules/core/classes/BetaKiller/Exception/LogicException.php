<?php

declare(strict_types=1);

namespace BetaKiller\Exception;

use BetaKiller\Exception;

class LogicException extends Exception
{
    public function __construct(string $message = null, array $variables = null)
    {
        parent::__construct($message ?? '', $variables, 500);
    }
}
