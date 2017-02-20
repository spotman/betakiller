<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Error_Php extends Controller_Developer
{
    use \BetaKiller\Helper\CurrentUserTrait;

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
            $content->set('developersList', $this->get_developers_list());

            // Покажем только обработанные ошибки
            $where["is_resolved"] = array('$in' => array(TRUE));

            $user_id_filter = (int) $this->request->query("user_id");

            if ( $user_id_filter )
            {
                $where["resolved_by"] = $user_id_filter;
            }

            // Текущий выбранный пользователь
            $content->set('userIdFilter', $user_id_filter);
        }
        else
        {
            // Покажем только требующие внимания ошибки
            $where["is_resolved"] = array('$in' => array(FALSE, NULL));
        }

        // Маркер показа только новых ошибок
        $content->set('showResolvedErrors', $show_resolved_errors);

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

        $errors = array();

        foreach ( $model->load($params) as $error ) /** @var Model_Error_Message_Php $error */
        {
            $showURL = "/errors/php/".$error->get_hash();

            $errors[] = array(
                'showURL'       =>  $showURL,
                'deleteURL'     =>  $showURL.'/delete',
                'resolveURL'    =>  $showURL.'/resolve',
                'paths'         =>  $error->get_paths(),
                'message'       =>  Text::limit_chars($error->get_message(), 120, '...', FALSE),
                'time'          =>  date("H:i:s d.m.Y", $error->get_time()),
                'module'        =>  $error->get_module(),
                'isResolved'    =>  $error->is_resolved(),
                'resolvedBy'    =>  $error->get_resolved_by(),
            );
        }

        $content->set('errors', $errors);
        $content->set('sortBy', $sort_by);
        $content->set('sortDirection', $sort_direction);
        $content->set('basePath', dirname(APPPATH));

        $content->set('sortByURL', '/errors/php/action/set_sort_by/');
        $content->set('toggleSortDirectionURL', '/errors/php/action/toggle_sort_direction');
        $content->set('toggleShowResolvedErrorsURL', '/errors/php/action/toggle_show_resolved_errors');

        Assets::instance()
            ->add('jquery')
            ->add('bootstrap');

        $this->send_view($content);
    }

    protected function get_developers_list()
    {
        $list = $this->current_user()->get_developers_list();
        $return = array();

        foreach ( $list as $user )
        {
            $return[ $user->get_id() ] = $user->get_full_name();
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
        $key = $this->param("param");
        $this->set_sort_by($key);
        $this->redirect("/errors/php/");
    }

    public function action_throw()
    {
        $code = $this->param("param");
        throw HTTP_Exception::factory((int) $code ?: 500, 'This is a test');
    }

    public function action_email()
    {
        $email = $this->current_user()->get_email();
        $emails_sent = (int) Email::send(NULL, $email, 'This is a test', 'Message test');

        echo 'Email sent: '.$emails_sent;
    }
}
