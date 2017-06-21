<?php defined('SYSPATH') OR die('No direct script access.');

abstract class HTTP_Exception_Expected extends Kohana_HTTP_Exception_Expected
{
    /**
     * Returns TRUE if someone must be notified about current exception type
     * Override this method with FALSE return if notification about exceptions of concrete class is not needed
     *
     * @example HTTP_Exception_Verbal
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return false;
    }
}
