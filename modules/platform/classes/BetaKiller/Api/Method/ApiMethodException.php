<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method;

use BetaKiller\Exception;

class ApiMethodException extends Exception
{
    /**
     * @return bool
     */
    public function showOriginalMessageToUser(): bool
    {
        return true;
    }
}
