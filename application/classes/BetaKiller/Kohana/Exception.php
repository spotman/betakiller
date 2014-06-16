<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Kohana_Exception extends Kohana_Kohana_Exception {

    /**
     * Адрес электронной почты, на который отправляются уведомления об ошибках
     */
    const NOTIFICATION_EMAIL = "i.am@spotman.ru";

    /**
     * Уведомления будут отсылаться при повторном появлении ошибки в N-ный раз
     */
    const NOTIFICATION_REPEAT_COUNT = 50;

    /**
     * Уведомления будут отсылаться не чаще чем T секунд
     */
    const NOTIFICATION_REPEAT_DELAY = 30;

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

    /**
     * Sending additional headers for better debugging
     * @param Exception $exception
     * @param Response $response
     */
    public static function add_debug_headers(Exception $exception, Response $response)
    {
        $response->headers('X-Exception-Class', get_class($exception));
        $response->headers('X-Exception-Message', $exception->getMessage());
    }

    static public function _handler(Exception $original_exception, $notify = TRUE)
    {
        // Регистрируем ошибку и выводим оригинальный стектрейс, если мы в режиме разработки или тестирования
        if ( ! Kohana::in_production() )
        {
            $response = parent::_handler($original_exception);
            static::add_debug_headers($original_exception, $response);
            return $response;
        }

        static::$_counter++;

        if ( static::$_counter > 10 )
        {
            static::log($original_exception);
            die('Too much exceptions (recursion)');
        }

        // Получаем оригинальный стектрейс
        $original_response = parent::response($original_exception);

        if ( $notify AND ($original_exception instanceof Kohana_Exception) )
        {
            $notify = $original_exception->is_notification_enabled();
        }

        // Информируем разработчиков, если это разрешено
        if ( $notify )
        {
            static::notify($original_exception, $original_response);
        }

        try
        {
            // Подготавливаем красивое сообщение об ошибке
            $response = self::make_nice_message( $original_exception );
        }
        catch(Exception $e)
        {
            // Или уведомляем разработчиков о нештатной ситуации
            static::log($original_exception);
            static::notify($e);
            $response = Response::factory()->status(500);
        }

        static::$_counter--;

        return $response;
    }

    /**
     * Уведомляет разработчиков о возникшей ошибке
     * @param Exception $e
     * @param Response $e_response
     */
    static public function notify(Exception $e, Response $e_response = NULL)
    {
        try
        {
            $load = sys_getloadavg();

            // Если нагрузка на сервер слишком большая, просто логируем ошибку в файл
            if ( array_shift($load) > static::get_cpu_core_count() )
            {
                static::log($e);
                return;
            }

            // Если не указан респонз с трейсом, получаем дефолтный трейс
            if ( $e_response === NULL )
            {
                $e_response = parent::response($e);
            }

            $e_class    = get_class($e);
            $e_code     = $e->getCode();
            $e_file     = $e->getFile();
            $e_line     = $e->getLine();

            // Полный путь до файла с ошибкой + номер строки
            $path = $e_file .":". $e_line;

            // Собираем сообщение и экранируем спецсимволы, чтобы минимизировать XSS
            $message = HTML::chars("$e_class [$e_code]: ". $e->getMessage());

            /** @var Model_Error_Message_Php $odm */
            $odm = Mango::factory("Error_Message_Php");

            // Вычисляем уникальный хеш сообщения
            $hash = $odm::make_hash($message);

            // Попробуем поискать в базе запись с таким же сообщением об ошибке
            $odm->find_by_hash($hash);

            // Если ошибка найдена
            if ( $odm->loaded() )
            {
                // Отмечаем ошибку как повторяющуюся
                $odm->mark_repeat();
            }
            // Если нет, создаём её
            else
            {
                $odm->hash = $hash;
                $odm->message = $message;

                $odm->urls = array();
                $odm->paths = array();

                $odm->create();

                // Отмечает ошибку как новую и требующую внимания разработчиков
                $odm->mark_new();
            }

            // Устанавливаем время последнего появления ошибки
            $odm->set_time();

            // Пробуем получить текущий uri
            $url = Request::current() ? Request::current()->detect_uri() : NULL;

            // Добавляем url документа, если его нет в списке
            $odm->add_url($url);

            // Добавляем путь с ошибкой, если его нет в списке
            $odm->add_path($path);

            // Добавляем стектрейс
            $odm->add_trace($e_response);

            $module = Request::current() ? Request::current()->module() : NULL;

            // Добавляем имя модуля, чтобы потом группировать исключения
            $odm->add_module($module);

            // Увеличиваем кол-во появлений текущей ошибки
            $odm->increment_counter();

            // Если это свежая ошибка или она повторяется
            if ( $odm->is_notification_needed(static::NOTIFICATION_REPEAT_COUNT, static::NOTIFICATION_REPEAT_DELAY) )
            {
                // Уведомляем разработчиков
                Email::send(
                    NULL,
                    static::NOTIFICATION_EMAIL,
                    "Kohana exception",
                    static::make_email($odm),
                    TRUE // is html
                );

                // Сохраняем время последнего уведомления об ошибке
                $odm->set_last_notification_time();
            }

            // Сохраняем данные
            $odm->update();
        }
        catch ( Exception $error )
        {
            // Если не удалось уведомить, пишем в лог все ошибки и тихонечко выходим
            static::log($e);
            static::log($error);
        }
    }

    static public function make_email(Model_Error_Message_Php $model)
    {
        $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost";

        ob_start();
?>
        <html>
            <head>
            </head>

            <body>
                <p>Новое исключение: <strong><?= $model->get_message() ?></strong></p>

                <p>
                    Встречается по следующим адресам:

                    <ul>
                    <? foreach ( $model->get_urls() as $url ): ?>
                        <li><a href="http://<?= $host . $url ?>"><?= $host . $url ?></a></li>
                    <? endforeach ?>
                    </ul><br />

                    в следующих файлах:

                    <ul>
                        <? foreach ( $model->get_paths() as $path ): ?>
                            <li><?= $path ?></li>
                        <? endforeach ?>
                    </ul><br />
                </p>

                <p><a href="http://<?= $host ?>/errors/php/<?= $model->get_hash() ?>"><strong>Стектрейс и другая информация здесь</strong></a></p>
            </body>
        </html>
<?
        return ob_get_clean();
    }

    /**
     * Возвращает контент красивого сообщения об ошибке
     * @param Exception $e
     * @return Response
     */
    static public function make_nice_message(Exception $e)
    {
        // Если это не наследник Kohana_Exception, оборачиваем его, чтобы показать базовое сообщение об ошибке
        if ( ! ( $e instanceof Kohana_Exception ) )
        {
            $e = new Kohana_Exception($e->getMessage(), NULL, $e->getCode(), $e);
        }

        $code = $e->getCode();

        // Определяем HTTP status code
        $http_code = ( $e instanceof HTTP_Exception ) ? $code : 500;

        $response = Response::factory()->status($http_code);

        try
        {
            // Получаем вьюшку для текущего исключения
            $view = $e->get_view();

            // Чтобы не было XSS, преобразуем спецсимволы
            $view->set('message', HTML::chars($e->get_user_message()));
            $view->set('code', (int) $code);

            $response->body($e->template($view)->render());
        }
        catch ( Exception $ex )
        {
            $response->status(500);
            static::log($ex);
        }

        return $response;
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

    protected function get_default_message()
    {
        return __('System error');
    }

    // TODO cross platform
    protected static function get_cpu_core_count()
    {
        $data = file('/proc/stat');
        $cores = 0;

        foreach ( $data as $line )
        {
            if ( preg_match('/^cpu[0-9]/', $line) )
            {
                $cores++;
            }
        }

        return $cores;
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