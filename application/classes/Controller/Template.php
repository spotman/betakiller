<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Controller_Template
 */

class Controller_Template extends Controller {

    /**
     * @var string|View_Template
     */
    public  $template = 'frontend';

    private $show_profiler = FALSE;


    public function before()
    {
        $this->init_i18n();

        // Init template
        if ( $this->template )
        {
            $this->template = View_Template::factory($this->template);
        }
    }

    /**
     * Локализация
     */
    protected function init_i18n()
    {
        // Смотрим, ести ли текущий язык в куке
        $user_lang = Cookie::get(i18n::COOKIE_NAME);

        if ( ! in_array($user_lang, array(NULL, "ru", "en")) )
            throw new HTTP_Exception_500('Unknown language :lang', array(':lang' => $user_lang));

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
                $user_lang = $headers->preferred_language(array('en', 'ru'));
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

    public function after()
    {
        // If template is disabled
        if ( $this->template === NULL )
        {
            // Getting clean output
            // TODO $output = $this->response->get_body(FALSE);
            $output = $this->response->body();
        }
        // If there is template, but current request is AJAX or HVMC
        elseif ( $this->request->is_ajax() OR ! $this->request->is_initial() )
        {
            // Getting content from template
            $output = $this->template->get_content();
        }
        // This is the regular request
        else
        {
            // Render template with its content
            $output = $this->template->render();
        }

        // TODO Request_Processor + Request_Processor_StaticFiles + adding processor to request
//        // Заменяем во всех ссылках указание на статические файлы
//        $this->response->body(
//            str_replace('{staticfiles_url}', STATICFILES_URL, $this->response->body())
//        );

        // Показываем профайлер, если он включён из консоли разработчика или принудительно из самого экшна
        if ( $this->show_profiler OR $this->is_profiler_enabled() )
        {
            $output .= Profiler::render();
        }

        $this->response->body($output);

        parent::after();
    }

//    protected function content_type($type = NULL)
//    {
//        return $this->response->content_type($type);
//    }

    protected function send_view(View $view)
    {
        if ( $this->template )
        {
            $this->template->set_content($view);
        }
        else
        {
            $this->send_string($view);
        }
    }

    /**
     * Отправляет текст в stdout в чистом виде, не обрамляя его в шаблон
     * @param $string Текст для отправки
     */
    protected function send_string($string)
    {
        $this->template = NULL;
        $this->response->send_string($string);
    }

    /**
     * Отправляет данные в stdout с помощью json_encode, отключая шаблон и правильно устанавливая заголовки
     * @param integer $result Результат обработки запроса (успех или ошибка) или строка / многомерный массив со структурой данных
     * @param mixed $data Строка / многомерный массив со структурой данных, если первым аргументом идёт результат типа bool
     */
    protected function send_json($result = Response::JSON_SUCCESS, $data = NULL)
    {
        $this->template = NULL;
        $this->response->send_json($result, $data);
    }

    /**
     * Sends response for JSONP request
     * @param array $data Raw data
     * @param string|null $callback_key JavaScript callback function key
     */
    protected function send_jsonp(array $data, $callback_key = NULL)
    {
        $this->template = NULL;
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

        $view = View::factory( strtolower($path . DIRECTORY_SEPARATOR . $file) );

        return $view;
    }


    /**
     * Включает профайлер для текущего экшна
     */
    protected function profiler()
    {
        $this->show_profiler = TRUE;
    }

    /**
     * Возвращает TRUE, если профайлер включен и должен быть показан в текущем запросе
     * @return bool
     */
    protected function is_profiler_enabled()
    {
        // Показываем профайлер только разработчикам и только если это не AJAX/HVMC запрос
        return ( $this->request->is_initial() AND ! $this->request->is_ajax()
            AND Env::user(TRUE) AND Env::user()->is_developer() AND Profiler::is_enabled() );
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

    /**
     * Хелпер для добавления библиотеки jquery на страницу
     * @return $this
     */
    protected function jquery()
    {
        JS::jquery();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.ui на страницу (вместе с локализацией)
     * @return $this
     */
    protected function jquery_ui()
    {
        JS::jquery_ui();
        CSS::jquery_ui();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.validate на страницу (вместе с локализацией)
     * @return $this
     */
    protected function jquery_validation()
    {
        JS::jquery_validation();
        CSS::jquery_validation();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.fileupload на страницу
     * @return $this
     */
    protected function jquery_fileupload()
    {
        JS::jquery_fileupload();
        CSS::jquery_fileupload();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.chosen на страницу
     * @return $this
     */
    protected function jquery_chosen()
    {
        JS::jquery_chosen();
        CSS::jquery_chosen();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.qtip на страницу
     * @link http://craigsworks.com/projects/qtip2/
     * @return $this
     */
    protected function jquery_qtip()
    {
        JS::jquery_qtip();
        CSS::jquery_qtip();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.pnotify на страницу
     * @link http://pinesframework.org/pnotify/
     * @return $this
     */
    protected function jquery_pnotify()
    {
        JS::jquery_pnotify();
        CSS::jquery_pnotify();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.jeditable на страницу
     * @link http://www.appelsiini.net/projects/jeditable
     * @return $this
     */
    protected function jquery_jeditable()
    {
        JS::jquery_jeditable();
        return $this;
    }

    /**
     * Хелпер для добавления плагина выбора времени
     * @link http://jonthornton.github.io/jquery-timepicker/
     * @return $this
     */
    protected function jquery_timepicker()
    {
        JS::jquery_timepicker();
        CSS::jquery_timepicker();
        return $this;
    }

    /**
     * Хелпер для добавления twitter bootstrap на страницу
     * @return $this
     */
    protected function bootstrap()
    {
        JS::bootstrap();
        CSS::bootstrap();
        return $this;
    }

    /**
     * Хелпер для добавления bootstrap диалоговых окон: алертов, конфирмов, промптов
     * @return $this
     */
    protected function bootstrap_bootbox()
    {
        JS::bootstrap_bootbox();
        return $this;
    }

    /**
     * Хелпер для добавления библиотеки underscore
     * @link http://underscorejs.org/
     * @return $this
     */
    protected function underscore()
    {
        JS::underscore();
        return $this;
    }

    /**
     * Хелпер для добавления редактора tinyMCE
     * @link http://www.tinymce.com/
     * @return $this
     */
    protected function tinyMCE()
    {
        JS::tinyMCE();
        return $this;
    }


}