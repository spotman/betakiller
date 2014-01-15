<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Error_Widget extends Controller_Developer {

    public function action_index()
    {
        $content = View::factory("error/widget/index");

        // Получаем общее количество ошибок
        $content->error_count = $this->get_php_errors_count();

        // Включен ли профайлер?
        $content->is_profiler_enabled = Profiler::is_enabled();

        $this->jquery()->jquery_pnotify();

        $this->_layout->content = $content;
    }

    protected function get_php_errors_count()
    {
        // На случай, если вдруг отвалится MongoDB
        try
        {
            return Mango::factory("Error_Message_Php")->collection_size();
        }
        catch ( Exception $e )
        {
            return NULL;
        }
    }

    /**
     * Включает / выключает профайлер
     */
    public function action_toggle_profiler()
    {
        if ( Profiler::is_enabled() )
        {
            Profiler::disable();
        }
        else
        {
            Profiler::enable();
        }
    }

}