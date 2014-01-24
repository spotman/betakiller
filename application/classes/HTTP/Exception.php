<? defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception extends Kohana_HTTP_Exception {

    /**
     * Возвращает красивое сообщение при пробросе исключения на продакшне
     * @return Response
     */
    public function get_response()
    {
        return parent::_handler($this);
    }

    /**
     * Возвращает объект вьюшки для текущего статуса ошибки
     * Переопредели этот метод в HTTP_Exception_xxx, если нужно взять вьюшку из другого места и с другим именем
     * @return View
     */
    public function get_view()
    {
        try
        {
            // Обнуляем view_path, чтобы оно не влияло на поиск вьюшки
            // View::reset_view_path();

            $code = $this->getCode();

            // Попробуем получить вьюшку для текущего статуса ошибки
            return View::factory($this->get_view_path($code))
                ->set('code', $code)
                ->set('message', HTML::chars($this->getMessage()));
        }
        catch ( Exception $e )
        {
            self::log($e);

            // Добавляем ошибку в консоль
            static::notify($e, parent::response($e));

            // Иначе показываем базовое сообщение
            return parent::get_view();
        }
    }

}