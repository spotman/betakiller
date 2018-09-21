<?php
declare(strict_types=1);

namespace BetaKiller\Api;

class ApiMethodException extends \Spotman\Api\ApiMethodException
{
    /**
     * @return bool
     */
    public function showOriginalMessageToUser(): bool
    {
        return true;
    }
}
