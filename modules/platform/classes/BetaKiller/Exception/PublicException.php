<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class PublicException extends BadRequestHttpException
{
    /**
     * If returns true, then original exception message will be shown to end-user in JSON and error pages
     * Override this method with *true* return if it's domain exception
     *
     * @return bool
     */
    public function showOriginalMessageToUser(): bool
    {
        return true;
    }

    /**
     * Returns TRUE if someone must be notified about current exception type
     * Override this method with *false* return if notification about exceptions of concrete class is not needed
     *
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return false;
    }
}
