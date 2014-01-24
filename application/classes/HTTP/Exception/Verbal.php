<? defined('SYSPATH') OR die('No direct script access.');

/**
 * Class HTTP_Exception_Verbal
 * Бросайте исключения этого типа, если нужно прервать выполнение скрипта и сообщить пользователю о проблеме
 * Исключение этого типа не логируется так как причиной проблемы является сам пользователь
 */
class HTTP_Exception_Verbal extends HTTP_Exception_500 {

    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    protected $_show_original_message_to_user = TRUE;

    /**
     * Отключаем уведомление разработчиков о данном типе эксепшнов
     */
    protected $_send_notification = FALSE;

    protected function get_view_path($file = NULL)
    {
        return parent::get_view_path('verbal');
    }

}