<?php defined('SYSPATH') OR die('No direct script access.');

class Assets_Exception_Upload extends Assets_Provider_Exception {

    // Show text of this message in JSON-response
    protected $_show_original_message_to_user = TRUE;

    /**
     * Отключаем уведомление разработчиков о данном типе эксепшнов
     */
    protected $_send_notification = FALSE;

}