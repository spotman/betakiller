<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Error_Php extends Controller_Developer {

    // public  $template = 'templates/frontend';

    const   SHOW_RESOLVED_COOKIE_NAME = "errors:show_resolved_errors";
    const   SORT_BY_COOKIE_NAME = "errors:sort_by";
    const   SORT_DIRECTION_COOKIE_NAME = "errors:sort_direction";

    /**
     * Показывает список ошибок в PHP
     */
    public function action_index()
    {
        /** @var Model_Error_Message_Php $model */
        $model = Mango::factory("Error_Message_Php");

        $content = $this->view();

        $show_resolved_errors = $this->get_show_resolved_errors();

        $where = array();

        if ( $show_resolved_errors )
        {
            $content->developers_list = $this->get_developers_list();

            // Покажем все ошибки
            $where["is_resolved"] = array('$in' => array(TRUE, FALSE, NULL));

            $user_id_filter = (int) $this->request->query("user_id");

            if ( $user_id_filter )
            {
                $where["resolved_by"] = $user_id_filter;
            }

            // Текущий выбранный пользователь
            $content->user_id_filter = $user_id_filter;
        }
        else
        {
            // Покажем только требующие внимания ошибки
            $where["is_resolved"] = array('$in' => array(FALSE, NULL));
        }

        // Маркер показа только новых ошибок
        $content->show_resolved_errors = $show_resolved_errors;

        // Определяем критерий сортировки
        $sort_by = $this->get_sort_by();

        $sort_direction = $this->get_sort_direction();

        $sort_multiplier = $sort_direction ? -1 : 1;

        switch ($sort_by)
        {
            case 'message':
                $sort_criteria = array("message" => 1 * $sort_multiplier);
                break;

            case 'module':
                $sort_criteria = array("module" => 1 * $sort_multiplier);
                break;

            default:
                $sort_criteria = array("time" => -1 * $sort_multiplier);
        }

        $params = array(
            "limit"     => NULL,
            "sort"      => $sort_criteria,
            "criteria"  => $where
        );

        $content->errors = $model->load($params);
        $content->sort_by = $sort_by;
        $content->sort_direction = $sort_direction;

        $this->jquery()->bootstrap();

        $this->send_view($content);
    }

    protected function get_developers_list()
    {
        $list = Env::user()->get_developers_list();
        $return = array();

        foreach ( $list as $user )
        {
            $return[ $user->pk() ] = $user->get_full_name();
        }

        return $return;
    }

    protected function set_show_resolved_errors($value)
    {
        Cookie::set(self::SHOW_RESOLVED_COOKIE_NAME, $value);
    }

    protected function get_show_resolved_errors()
    {
        return Cookie::get(self::SHOW_RESOLVED_COOKIE_NAME, FALSE);
    }

    protected function set_sort_by($key)
    {
        Cookie::set(self::SORT_BY_COOKIE_NAME, $key);
    }

    protected function get_sort_by()
    {
        return Cookie::get(self::SORT_BY_COOKIE_NAME);
    }

    protected function set_sort_direction($direction)
    {
        Cookie::set(self::SORT_DIRECTION_COOKIE_NAME, $direction);
    }

    protected function get_sort_direction()
    {
        return Cookie::get(self::SORT_DIRECTION_COOKIE_NAME, FALSE);
    }

    public function action_toggle_show_resolved_errors()
    {
        $this->set_show_resolved_errors( ! $this->get_show_resolved_errors() );
        $this->redirect("/errors/php/");
    }

    public function action_toggle_sort_direction()
    {
        $this->set_sort_direction( ! $this->get_sort_direction() );
        $this->redirect("/errors/php/");
    }

    public function action_set_sort_by()
    {
        $key = $this->request->param("param");
        $this->set_sort_by($key);
        $this->redirect("/errors/php/");
    }
}