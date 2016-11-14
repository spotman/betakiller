<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Controller
 * Basic controller with common helpers
 */
abstract class Controller extends Controller_Proxy
{
    use BetaKiller\Helper\Base;

    const JSON_SUCCESS = Response::JSON_SUCCESS;
    const JSON_ERROR = Response::JSON_ERROR;

    protected static $_after_callbacks = array();

    /**
     * Getter/setter for request
     *
     * @param Request $request
     * @return Request|Controller
     */
    public function request(Request $request = NULL)
    {
        if ( $request === NULL )
            return $this->request;

        $this->request = $request;
        return $this;
    }

    /**
     * Getter/setter for response
     *
     * @param Response $response
     * @return Response|Controller
     */
    public function response(Response $response = NULL)
    {
        if ( $response === NULL )
            return $this->response;

        $this->response = $response;
        return $this;
    }

    public function before()
    {
        parent::before();

        $this->check_connection_protocol();

        $this->init_i18n();
    }

    /**
     * Checks for current protocol and makes redirect if it`s not equal to the base protocol
     */
    protected function check_connection_protocol()
    {
        $base_protocol = parse_url(Kohana::$base_url, PHP_URL_SCHEME);

        $is_secure_needed = ($base_protocol == 'https');

        $is_secure = $this->request->secure();

        if ( ($is_secure_needed && !$is_secure) || ($is_secure && !$is_secure_needed))
        {
            $url = $this->request->url($base_protocol);
            $this->redirect($url);
        }
    }

    /**
     * I18n initialization
     *
     * @throws HTTP_Exception_500
     */
    protected function init_i18n()
    {
        // Get lang from cookie
        $user_lang = Cookie::get(I18n::COOKIE_NAME);

        $allowed_languages = I18n::lang_list();

        if ($user_lang AND ! in_array($user_lang, $allowed_languages) )
            throw new HTTP_Exception_500(
                'Unknown language :lang, only these are allowed: :allowed',
                array(':lang' => $user_lang, ':allowed' => implode(', ', $allowed_languages))
            );

        // If current lang is not set
        if ( ! $user_lang )
        {
            // Get current user
            $user = $this->current_user(TRUE);

            // If user is authorized
            if ( $user )
            {
                // Get its lang
                $user_lang = $user->get_language_name();
            }
            // Else detect the preferred lang
            else
            {
                /** @var HTTP_Header $headers */
                $headers = $this->request->headers();
                $user_lang = $headers->preferred_language($allowed_languages);
            }
        }

        // Store lang in cookie
        Cookie::set(I18n::COOKIE_NAME, $user_lang);

        // Set I18n lang
        I18n::lang($user_lang);

        // Save all absent keys if in development env
        if ( ! Kohana::in_production(TRUE) )
        {
            register_shutdown_function(array("I18n", "write"));
        }
    }

    public function after()
    {
        parent::after();

        foreach ( static::$_after_callbacks as $callback )
        {
            call_user_func($callback, $this);
        }

        $this->response->check_if_not_modified_since();
    }

    /**
     * Helper for Request::param()
     *
     * @param string|null $key
     * @param string|null $default
     * @return mixed
     */
    protected function param($key = NULL, $default = NULL)
    {
        return $this->request->param($key, $default);
    }

    protected function post($key = NULL)
    {
        return $this->request->post($key);
    }

    protected function query($key = NULL)
    {
        return $this->request->query($key);
    }

    protected function is_ajax()
    {
        return $this->request->is_ajax();
    }

    /**
     * Getter/setter for Response content-type
     * Use this method for better uncaught exception handling
     *
     * @param int|null $type
     * @return int|Response
     */
    private function content_type($type = NULL)
    {
        return $this->response->content_type($type);
    }

    /**
     * Helper for better encapsulation of Response
     */
    protected function content_type_json()
    {
        $this->content_type(Response::JSON);
    }

    /**
     * Helper for setting "Last-Modified" header
     * @param DateTime $dateTime
     */
    protected function last_modified(DateTime $dateTime)
    {
        $this->response->last_modified($dateTime);
    }

    /**
     * Helper for setting "Expires" header
     * @param DateTime $dateTime
     */
    protected function expires(DateTime $dateTime)
    {
        $this->response->expires($dateTime);
    }

    /**
     * View Factory for current request (directory/controller/action)
     *
     * @param string|null $file View file path (relative to directory/controller)
     * @return View
     */
    protected function view($file = NULL)
    {
        $path = $this->request->directory()
            ? $this->request->directory() . DIRECTORY_SEPARATOR . $this->request->controller()
            : $this->request->controller();

        $file = $file ?: $this->request->action();

        $view = $this->view_factory($path . DIRECTORY_SEPARATOR . $file);

        return $view;
    }

    /**
     * Helper for View::factory()
     *
     * @param string $file
     * @param array $data
     * @return View
     */
    protected function view_factory($file = NULL, array $data = NULL)
    {
        return View::factory($file, $data);
    }

    /**
     * Sends plain text to stdout without wrapping it by template
     *
     * @param string $string Plain text for output
     * @param int $content_type Content type constant like Response::HTML
     */
    protected function send_string($string, $content_type = Response::HTML)
    {
        $this->response->send_string($string, $content_type);
    }

    /**
     * Helper for sending view to Response
     *
     * @param View $view
     */
    protected function send_view(View $view)
    {
        $this->send_string($view);
    }

    /**
     * Sends JSON response to stdout
     *
     * @param integer $result JSON result constant or raw data
     * @param mixed $data Raw data to send, if the first argument is constant
     */
    protected function send_json($result = self::JSON_SUCCESS, $data = NULL)
    {
        $this->response->send_json($result, $data);
    }

    protected function send_success_json($data)
    {
        $this->send_json(self::JSON_SUCCESS, $data);
    }

    protected function send_error_json($data)
    {
        $this->send_json(self::JSON_ERROR, $data);
    }

    /**
     * Sends response for JSONP request
     *
     * @param array $data Raw data
     * @param string|null $callback_key JavaScript callback function key
     */
    protected function send_jsonp(array $data, $callback_key = NULL)
    {
        $this->response->send_jsonp($data, $callback_key);
    }

    /**
     * Sends file to STDOUT for viewing or downloading
     *
     * @param string $content String content of the file
     * @param string $mime_type MIME-type
     * @param string $alias File name for browser`s "Save as" dialog
     * @param bool $force_download
     * @throws HTTP_Exception_500
     */
    protected function send_file($content, $mime_type = NULL, $alias = NULL, $force_download = FALSE)
    {
        if ( ! $content )
            throw new HTTP_Exception_500('Content is empty');

        $response = $this->response();

        $response->body($content);

        $response->headers('Content-Type', $mime_type ?: 'application/octet-stream');
        $response->headers('Content-Length', strlen($content) );

        if ( $force_download )
        {
            $response->headers('Content-Disposition', 'attachment; filename='.$alias);
        }
    }
//
//    public static function bind_after(callable $callback)
//    {
//        static::$_after_callbacks[] = $callback;
//    }
}
