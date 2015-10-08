<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Controller_Template
 */

class Controller_Basic extends Controller {

    /**
     * @var string|View_Layout
     */
    protected $_layout = 'default';

    private $show_profiler = FALSE;


    public function before()
    {
        parent::before();

        // Init template
        if ( $this->_layout )
        {
            $this->_layout = $this->layout_factory($this->_layout);
        }
    }

    /**
     * Twig layout factory
     *
     * @param $template_name
     * @return View_Layout
     */
    protected function layout_factory($template_name)
    {
        return View_Layout::factory($template_name);
    }

    public function after()
    {
        // If template is disabled
        if ( $this->_layout === NULL )
        {
            // Getting clean output
            $output = $this->response->body();
        }
        // If there is template, but current request is AJAX or HVMC
        elseif ( $this->request->is_ajax() OR ! $this->request->is_initial() )
        {
            // Getting content from template
            $output = $this->_layout->get_content();
        }
        // This is the regular request
        else
        {
            // Render template with its content
            $output = $this->_layout->render();

            $output = View_Wrapper::factory()
                ->set_content($output)
                ->render();
        }

        // Показываем профайлер, если он включён из консоли разработчика или принудительно из самого экшна
        if ( $this->show_profiler OR $this->is_profiler_enabled() )
        {
            $output .= Profiler::render();
        }

        $this->response->body($output);

        parent::after();
    }

    protected function send_view(View $view)
    {
        if ( $this->_layout )
        {
            // Render view for adding js/css files, described in it
            $this->_layout->set_content((string) $view);
        }
        else
        {
            parent::send_view($view);
        }
    }

    /**
     * Sends plain text to stdout without wrapping it by template
     *
     * @param string $string Plain text for output
     * @param int $content_type Content type constant like Response::HTML
     */
    protected function send_string($string, $content_type = Response::HTML)
    {
        $this->_layout = NULL;
        parent::send_string($string, $content_type);
    }

    /**
     * Sends JSON response to stdout
     *
     * @param integer $result JSON result constant or raw data
     * @param mixed $data Raw data to send, if the first argument is constant
     */
    protected function send_json($result = Response::JSON_SUCCESS, $data = NULL)
    {
        $this->_layout = NULL;
        parent::send_json($result, $data);
    }

    /**
     * Sends response for JSONP request
     *
     * @param array $data Raw data
     * @param string|null $callback_key JavaScript callback function key
     */
    protected function send_jsonp(array $data, $callback_key = NULL)
    {
        $this->_layout = NULL;
        parent::send_jsonp($data, $callback_key);
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
     * Helper for Twig::factory(), replaces all php views with twig ones
     *
     * @param string $file
     * @param array $data
     * @return Twig
     */
    protected function view_factory($file = NULL, array $data = NULL)
    {
        return Twig::factory($file, $data);
    }

}
