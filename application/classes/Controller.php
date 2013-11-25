<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Controller
 * Basic controller with common helpers
 */
abstract class Controller extends Controller_Proxy {

    /**
     * Getter/setter for request
     * @param Request $request
     * @return $this|Request
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
     * @param Response $response
     * @return $this|Response
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

        $this->init_i18n();
    }

    /**
     * I18n initialization
     * @throws HTTP_Exception_500
     */
    protected function init_i18n()
    {
        // Смотрим, ести ли текущий язык в куке
        $user_lang = Cookie::get(i18n::COOKIE_NAME);

        $allowed_languages = i18n::lang_list();

        if ($user_lang AND ! in_array($user_lang, $allowed_languages) )
            throw new HTTP_Exception_500(
                'Unknown language :lang, only these are allowed: :allowed',
                array(':lang' => $user_lang, ':allowed' => implode(', ', $allowed_languages))
            );

        // Если нет, получаем язык пользователя
        if ( ! $user_lang )
        {
            $user = Env::user(TRUE);

            // Если пользователь авторизован
            if ( $user )
            {
                // Получаем его язык
                $user_lang = $user->get_language_name();

                // И устанавливаем куку
                Cookie::set(i18n::COOKIE_NAME, $user_lang);
            }
            // Иначе выбираем наиболее подходящий язык
            else
            {
                /** @var HTTP_Header $headers */
                $headers = $this->request->headers();
                $user_lang = $headers->preferred_language($allowed_languages);
            }
        }

        // Устанавливаем язык для перевода
        I18n::lang($user_lang);

        // Если мы в режиме разработки
        if ( ! Kohana::in_production() )
        {
            // Сохраняем все непереведённые строки из пользовательского интерфейса
            register_shutdown_function(array("I18n", "write"));
        }
    }

    /**
     * Helper for Request::param()
     * @param string|null $key
     * @param string|null $default
     * @return mixed
     */
    protected function param($key = NULL, $default = NULL)
    {
        return $this->request->param($key, $default);
    }


    /**
     * View Factory for current request (directory/controller/action)
     * @param string|null $file View file path (relative to directory/controller)
     * @return View
     */
    protected function view($file = NULL)
    {
        $path = $this->request->directory()
            ? $this->request->directory() . DIRECTORY_SEPARATOR . $this->request->controller()
            : $this->request->controller();

        $file = $file ?: $this->request->action();

        $view = $this->view_factory( strtolower($path . DIRECTORY_SEPARATOR . $file) );

        return $view;
    }

    /**
     * Helper for View::factory()
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
     * @param string $string Plain text for output
     * @param int $content_type Content type constant like Response::HTML
     */
    protected function send_string($string, $content_type = Response::HTML)
    {
        $this->response->send_string($string, $content_type);
    }

    /**
     * Helper for sending view to Response
     * @param View $view
     */
    protected function send_view(View $view)
    {
        $this->send_string($view);
    }

    /**
     * Sends JSON response to stdout
     * @param integer $result JSON result constant or raw data
     * @param mixed $data Raw data to send, if the first argument is constant
     */
    protected function send_json($result = Response::JSON_SUCCESS, $data = NULL)
    {
        $this->response->send_json($result, $data);
    }

    /**
     * Sends response for JSONP request
     * @param array $data Raw data
     * @param string|null $callback_key JavaScript callback function key
     */
    protected function send_jsonp(array $data, $callback_key = NULL)
    {
        $this->response->send_jsonp($data, $callback_key);
    }


    /**
     * Отправляет файл в stdout для скачивания, с правильными заголовками
     * @param string $path Директория, в которой лежит файл
     * @param string $filename Имя файла в файловой системе
     * @param string $mime_type Тип файла, который будет отправлен в stdout
     * @param string $alias Имя файла, под которым файл появится у пользователя в браузере
     * @throws HTTP_Exception_404
     * @todo refactor to Request::send_file
     */
    protected function send_file($path, $filename, $mime_type = NULL, $alias = NULL)
    {
        $fullpath = rtrim($path, '/') .DIRECTORY_SEPARATOR . $filename;

        $content = @ file_get_contents($fullpath);

        if ( ! $content )
            throw new HTTP_Exception_404();

        if ( ! $mime_type )
        {
            $mime_type = "application/octet-stream";
        }

        if ( ! $alias )
        {
            $alias = $filename;
        }

        $this->response->headers('Content-Type', $mime_type);
        $this->response->headers("Content-Disposition", "attachment; filename=$alias");

        $this->send_string($content);
    }


    /**
     * Хелпер для добавления на страницу файла скрипта из директории static-files
     * @param $filename
     * @return $this
     */
    protected function add_script($filename)
    {
        $this->add_static_script($filename);
        return $this;
    }

    /**
     * Хелпер для добавления на страницу файла стиля из директории static-files
     * @param $filename
     * @return $this
     */
    protected function add_style($filename)
    {
        $this->add_static_style($filename);
        return $this;
    }

    /**
     * Хелпер для добавления на страницу файла скрипта по URL
     * @param $filename
     * @return $this
     */
    protected function add_public_script($filename)
    {
        JS::add_public($filename);
        return $this;
    }

    /**
     * Хелпер для добавления на страницу файла стиля по URL
     * @param $filename
     * @return $this
     */
    protected function add_public_style($filename)
    {
        CSS::add_public($filename);
        return $this;
    }

    /**
     * Хелпер для добавления на страницу файла скрипта, размещённого в одной из директорий static-files
     * @param $filename
     * @return $this
     */
    protected function add_static_script($filename)
    {
        JS::add_static($filename);
        return $this;
    }

    /**
     * Хелпер для добавления на страницу файла стиля, размещённого в одной из директорий static-files
     * @param $filename
     * @return $this
     */
    protected function add_static_style($filename)
    {
        CSS::add_static($filename);
        return $this;
    }

}
