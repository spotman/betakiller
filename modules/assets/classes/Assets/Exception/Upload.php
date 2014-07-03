<?php defined('SYSPATH') OR die('No direct script access.');

class Assets_Exception_Upload extends Assets_Provider_Exception {

    /**
     * Отключаем уведомление разработчиков о данном типе эксепшнов
     */
    public function is_notification_enabled()
    {
        return FALSE;
    }

    /**
     * Show text of this message in JSON-response
     */
    protected function show_original_message_to_user()
    {
        return TRUE;
    }

}