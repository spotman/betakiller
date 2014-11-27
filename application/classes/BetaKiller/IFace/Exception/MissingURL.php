<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_IFace_Exception_MissingURL extends Core_IFace_Exception_MissingURL {

    /**
     * Enable notification (we need to be notified about missing and incorrect URLs)
     * @return bool
     */
    public function is_notification_enabled()
    {
        return TRUE;
    }

}
