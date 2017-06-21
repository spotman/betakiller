<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\AbstractHttpErrorIFace;
use BetaKiller\IFace\IFaceFactory;

class BetaKiller_Kohana_Exception extends Kohana_Kohana_Exception
{
    /**
     * Exception counter for preventing recursion
     */
    protected static $_counter = 0;

    public function __construct($message = '', array $variables = null, $code = 0, Throwable $previous = null)
    {
        // Set up default message text if it was not set
        $message = $message ?: $this->getDefaultMessageI18nKey();

        parent::__construct($message, $variables, $code, $previous);
    }

    /**
     * @param Throwable $exception
     *
     * @return Response
     * @throws Kohana_Exception
     * @todo Rewrite to ExceptionHandler and move exception handling logic to it
     */
    public static function _handler(Throwable $exception)
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

        // Hack for CLI mode
        if (PHP_SAPI === 'cli') {
            if (!$notify) {
                echo self::text($exception);
            }

            return null;
        }

        // Make nice message if allowed or use default Kohana response
        $response = self::makeNiceMessage($exception) ?: parent::response($exception);

        static::$_counter--;

        return $response;
    }

    /**
     * Возвращает контент красивого сообщения об ошибке
     *
     * @param \Throwable $exception
     *
     * @return Response|null
     */
    public static function makeNiceMessage(Throwable $exception): ?Response
    {
        // Prevent displaying custom error pages for expected exceptions (301, 302, etc)
        if (($exception instanceof HTTP_Exception_Expected) && !$exception->alwaysShowNiceMessage()) {
            return $exception->get_response();
        }

        $alwaysShowNiceMessage = ($exception instanceof self)
            ? $exception->alwaysShowNiceMessage()
            : false;

        if (!$alwaysShowNiceMessage && !Kohana::in_production(true)) {
            return null;
        }

        // Если это не наследник Kohana_Exception, оборачиваем его, чтобы показать базовое сообщение об ошибке
        if (!($exception instanceof Kohana_Exception)) {
            $exception = new Kohana_Exception($exception->getMessage(), null, $exception->getCode(), $exception);
        }

        $response = Response::factory();
        $httpCode = self::getHttpErrorCode($exception);

        try {
            $iface = self::getErrorIFaceForCode($httpCode);

            $body = $iface
                ? $iface->setException($exception)->render()
                : self::renderDefaultMessage($exception);

            $response->status($httpCode)->body($body);
        } catch (Throwable $e) {
            $response->status(500);
            static::log($e);
        }

        return $response;
    }

    private static function getErrorIFaceForCode(int $code): ?AbstractHttpErrorIFace
    {
        // Try to find IFace provided code first and use default IFace if failed
        foreach ([$code, 500] as $tryCode) {
            if ($iface = static::createErrorIFaceFromCode($tryCode)) {
                return $iface;
            }
        }

        return null;
    }

    /**
     * @param int $code
     *
     * @return \BetaKiller\IFace\AbstractHttpErrorIFace|null
     */
    private static function createErrorIFaceFromCode(int $code): ?AbstractHttpErrorIFace
    {
        try {
            return static::createIFaceFromCodename('HttpError'.$code);
        } catch (Throwable $e) {
            static::log($e);

            return null;
        }
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\AbstractHttpErrorIFace|mixed
     */
    private static function createIFaceFromCodename(string $codename): ?AbstractHttpErrorIFace
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

    private static function renderDefaultMessage(Throwable $e): string
    {
        if ($userMessage = self::getUserMessage($e)) {
            // Prevent XSS
           return HTML::chars($userMessage);
        }

        $key = self::getErrorLabelI18nKey($e);

        return __($key);
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
     *
     * @param \Throwable $e
     *
     * @return null|string
     */
    public static function getUserMessage(Throwable $e)
    {
        $show = ($e instanceof self) && $e->showOriginalMessageToUser();

        return $show ? $e->getMessage() : null;
    }

    public static function getErrorLabelI18nKey(Throwable $e)
    {
        $code = static::getHttpErrorCode($e);

        return static::getLabelI18nKeyForCode($code);
    }

    private static function getHttpErrorCode(Throwable $e)
    {
        return ($e instanceof HTTP_Exception) ? $e->getCode() : 500;
    }

    private static function getLabelI18nKeyForCode(int $code)
    {
        return 'error.'.$code.'.label';
    }

    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     *
     * @return string
     */
    protected function getDefaultMessageI18nKey()
    {
        return static::getLabelI18nKeyForCode(500);
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
