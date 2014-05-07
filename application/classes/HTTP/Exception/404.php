<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {

    /**
     * Отключаем уведомление о текущем типе исключений
     * @return bool
     */
    public function is_notification_enabled()
    {
        return FALSE;
    }

    protected function get_default_message()
    {
        return __('Not found');
    }

}