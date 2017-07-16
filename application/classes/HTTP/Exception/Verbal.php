<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class HTTP_Exception_Verbal
 * Throw exceptions of the current type if you need to stop script execution and send message to the user.
 * Notifications are disabled for this type coz throwing this kind of exception is a normal flow.
 */
class HTTP_Exception_Verbal extends HTTP_Exception_400
{
    /**
     * @return bool
     */
    protected function showOriginalMessageToUser(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return false;
    }
}
