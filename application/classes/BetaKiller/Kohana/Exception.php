<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\IFaceFactory;

class BetaKiller_Kohana_Exception extends Kohana_Kohana_Exception
{
    /**
     * Exception counter for preventing recursion
     */
    protected static $_counter = 0;

    public function __construct($message = '', array $variables = null, $code = 0, Exception $previous = null)
    {
        // Set up default message text if it was not set
        $message = $message ?: $this->getDefaultMessage();

        parent::__construct($message, $variables, $code, $previous);
    }

    /**
     * @param Throwable $exception
     *
     * @return Response
     * @throws Kohana_Exception
     * @todo Rewrite to ExceptionHandler and move exception handling logic to it
     */
    static public function _handler(Throwable $exception)
    {
        static::$_counter++;

        if (static::$_counter > 10) {
            static::log(new static('Too much exceptions (recursion) for :msg', [':msg' => self::text($exception)]));
            die();
        }

        $notify = ($exception instanceof self)
            ? $exception->isNotificationEnabled()
            : true;

        if ($notify) {
            // Logging exception
            static::log($exception);
        }

        if (PHP_SAPI === 'cli') {
            if (!$notify) {
                echo self::text($exception);
            }

            // Exception already processed via Minion_Log
            exit(1);
        }

        $alwaysShowNiceMessage = ($exception instanceof self)
            ? $exception->alwaysShowNiceMessage()
            : false;

        if ($alwaysShowNiceMessage || Kohana::in_production(true)) {
            $response = self::makeNiceMessage($exception);
        } else {
            // Use default Kohana response
            $response = parent::response($exception);
        }

        static::$_counter--;

        return $response;
    }

    /**
     * Возвращает контент красивого сообщения об ошибке
     *
     * @param Exception $exception
     *
     * @return Response
     */
    static public function makeNiceMessage(Exception $exception)
    {
        // Prevent displaying custom error pages for expected exceptions (301, 302, 401, 403, etc)
        if (($exception instanceof HTTP_Exception_Expected) && !$exception->alwaysShowNiceMessage()) {
            return $exception->get_response();
        }

        // Если это не наследник Kohana_Exception, оборачиваем его, чтобы показать базовое сообщение об ошибке
        if (!($exception instanceof Kohana_Exception)) {
            $exception = new Kohana_Exception($exception->getMessage(), null, $exception->getCode(), $exception);
        }

        $response = Response::factory();

        try {
            $code     = $exception->getCode();
            $httpCode = ($exception instanceof HTTP_Exception) ? $code : 500;

            $response
                ->status($httpCode)
                ->body($exception->renderCustomMessage($httpCode) ?: $exception->renderDefaultMessage($httpCode));
        } catch (Throwable $e) {
            $response->status(500);
            static::log($e);
        }

        return $response;
    }

    public function renderCustomMessage($code)
    {
        try {
            return $this->getIFaceFromCode($code)->render();
        } catch (Throwable $e) {
            static::log($e);

            return null;
        }
    }

    /**
     * @param int $code
     *
     * @return \BetaKiller\IFace\IFaceInterface|mixed
     */
    protected function getIFaceFromCode($code)
    {
        return $this->createIFaceFromCodename('Error'.$code);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface|mixed
     */
    protected function createIFaceFromCodename(string $codename)
    {
        $factory = \BetaKiller\DI\Container::getInstance()->get(IFaceFactory::class);

        return $factory->fromCodename($codename);
    }

    /**
     * Overwrite this method with "return TRUE" to show custom message in all cases
     *
     * @return bool
     */
    protected function alwaysShowNiceMessage()
    {
        return false;
    }

    public function renderDefaultMessage($code)
    {
        // Получаем вьюшку для текущего исключения
        $view = $this->getView();

        // Чтобы не было XSS, преобразуем спецсимволы
        $view->set('message', HTML::chars($this->getUserMessage()));
        $view->set('code', (int)$code);

        return $this->template($view)->render();
    }

    /**
     * Обрамляет вьюшку в базовый шаблон ошибки
     *
     * @param View $error
     *
     * @return View
     */
    public function template(View $error)
    {
        return View::factory($this->getViewPath('template'), ['error' => $error]);
    }

    /**
     * Returns basic error view
     *
     * @return View
     */
    public function getView()
    {
        return View::factory($this->getViewPath());
    }

    /**
     * @param null|integer|string $file HTTP code number or filename (without extension) of error view
     *
     * @return string
     */
    protected function getViewPath($file = null)
    {
        return 'error-pages/'.($file ?: 500);
    }

    /**
     * Returns TRUE if someone must be notified about current exception type
     * Override this method with FALSE return if notification about exceptions of concrete class is not needed
     *
     * @example HTTP_Exception_Verbal
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return true;
    }

    /**
     * Returns text which would be shown to user on uncaught exception
     * For most of exception classes it returns NULL (we do not want to inform user about our problems)
     * For populating original message to user set up protected property $_show_original_message_to_user of your custom exception class
     *
     * @param bool $force_show Show original message
     *
     * @return null|string
     */
    public function getUserMessage($force_show = false)
    {
        return ($force_show || $this->showOriginalMessageToUser())
            ? $this->getMessage()
            : null;
    }

    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     *
     * @return string
     */
    protected function getDefaultMessage()
    {
        return 'System error';
    }

    /**
     * Показывать ли пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     *
     * @return bool
     */
    protected function showOriginalMessageToUser()
    {
        return false;
    }
}
