<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_403 extends Kohana_HTTP_Exception_403 {

    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    protected $_show_original_message_to_user = TRUE;

    /**
     * Отключаем уведомление о текущем типе исключений
     * @var bool
     */
    protected $_send_notification = FALSE;

}