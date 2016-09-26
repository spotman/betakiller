<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Kohana_Exception extends Kohana_Kohana_Exception
{
    use BetaKiller\Helper\Base;

    /**
     * Exception counter for preventing recursion
     */
    protected static $_counter = 0;

    public function __construct($message = "", array $variables = NULL, $code = 0, Exception $previous = NULL)
    {
        // Set up default message text if it was not set
        $message = $message ?: $this->get_default_message();

        parent::__construct($message, $variables, $code, $previous);
    }

//    /**
//     * Sending additional headers for better debugging
//     * @param Exception $exception
//     * @param Response $response
//     * @deprecated
//     */
//    public static function add_debug_headers(Exception $exception, Response $response)
//    {
//        $response->headers('X-Exception-Class', get_class($exception));
//        $response->headers('X-Exception-Message', $exception->getMessage());
//    }

    /**
     * @param Exception $exception
     * @return Response
     * @throws Kohana_Exception
     */
    static public function _handler(Exception $exception)
    {
        static::$_counter++;

        // Logging exception
        static::log($exception);

        if ( static::$_counter > 10 )
        {
            static::log(new static('Too much exceptions (recursion)'));
            die();
        }

        $always_show_nice_message = ($exception instanceof Kohana_Exception)
            ? $exception->always_show_nice_message()
            : FALSE;

        if ( Kohana::in_production(TRUE) OR $always_show_nice_message )
        {
            $response = self::make_nice_message($exception);
        }
        else
        {
            // Use default Kohana response
            $response = parent::response($exception);
//            static::add_debug_headers($exception, $response);
        }

        static::$_counter--;

        return $response;
    }

    /**
     * Возвращает контент красивого сообщения об ошибке
     * @param Exception $exception
     * @return Response
     */
    static public function make_nice_message(Exception $exception)
    {
        // Prevent displaying custom error pages for expected exceptions (301, 302, 401, 403, etc)
        if ( ($exception instanceof HTTP_Exception_Expected) AND ! $exception->always_show_nice_message() )
            return $exception->get_response();

        // Если это не наследник Kohana_Exception, оборачиваем его, чтобы показать базовое сообщение об ошибке
        if ( ! ( $exception instanceof Kohana_Exception ) )
        {
            $exception = new Kohana_Exception($exception->getMessage(), NULL, $exception->getCode(), $exception);
        }

        $response = Response::factory();

        try
        {
            $code = $exception->getCode();
            $http_code = ( $exception instanceof HTTP_Exception ) ? $code : 500;

            $response
                ->status($http_code)
                ->body($exception->render_custom_message($http_code) ?: $exception->render_default_message($http_code));
        }
        catch ( Exception $e )
        {
            $response->status(500);
            static::log($e);
        }

        return $response;
    }

    public function render_custom_message($code)
    {
        try
        {
            return $this->get_iface($code)->render();
        }
        catch ( Exception $e )
        {
            static::log($e);
            return NULL;
        }
    }

    /**
     * @param int $code
     * @return \BetaKiller\IFace\IFace
     */
    protected function get_iface($code)
    {
        return $this->iface_from_codename('Error_'.$code);
    }

    /**
     * Overwrite this method with "return TRUE" to show custom message in all cases
     * @return bool
     */
    protected function always_show_nice_message()
    {
        return FALSE;
    }

    public function render_default_message($code)
    {
        // Получаем вьюшку для текущего исключения
        $view = $this->get_view();

        // Чтобы не было XSS, преобразуем спецсимволы
        $view->set('message', HTML::chars($this->get_user_message()));
        $view->set('code', (int) $code);

        return $this->template($view)->render();
    }

    /**
     * Обрамляет вьюшку в базовый шаблон ошибки
     * @param View $error
     * @return View
     */
    public function template(View $error)
    {
        // Обнуляем view_path, чтобы оно не влияло на поиск вьюшки
        // View::reset_view_path();
        return View::factory($this->get_view_path('template'), array('error' => $error));
    }

    /**
     * Returns basic error view
     * @return View
     */
    public function get_view()
    {
        // Обнуляем view_path, чтобы оно не влияло на поиск вьюшки
        // View::reset_view_path();
        return View::factory($this->get_view_path());
    }

    /**
     * @param null|integer|string $file HTTP code number or filename (without extension) of error view
     * @return string
     */
    protected function get_view_path($file = NULL)
    {
        return 'error-pages/'. ($file ?: 500);
    }

    /**
     * Returns TRUE if someone must be notified about current exception type
     * Override this method with FALSE return if notification about exceptions of concrete class is not needed
     *
     * @example HTTP_Exception_Verbal
     * @return bool
     */
    public function is_notification_enabled()
    {
        return TRUE;
    }

    /**
     * Returns text which would be shown to user on uncaught exception
     * For most of exception classes it returns NULL (we do not want to inform user about our problems)
     * For populating original message to user set up protected property $_show_original_message_to_user of your custom exception class
     *
     * @param bool $force_show Show original message
     * @return null|string
     */
    public function get_user_message($force_show = FALSE)
    {
        return ( $force_show OR $this->show_original_message_to_user() )
            ?  $this->getMessage()
            : NULL;
    }

    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     *
     * @return string
     */
    protected function get_default_message()
    {
        return __('System error');
    }

    /**
     * Показывать ли пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     * @return bool
     */
    protected function show_original_message_to_user()
    {
        return FALSE;
    }

}
