<?php

class Model_Error_Message_Base extends Mango {

    protected $_db = "development";

    public static function make_hash($message)
    {
        return sha1($message);
    }

    public function get_hash()
    {
        return $this->hash;
    }

    public function find_by_hash($hash)
    {
        $this->values(array("hash" => $hash));
        $this->load();
    }

    public function get_counter()
    {
        return ( $this->loaded() AND $this->total ) ? (int) $this->total->as_int() : 0;
    }

    public function increment_counter()
    {
        $this->total->increment();
        return $this;
    }

    public function add_path($path)
    {
        // Если такой путь уже есть в ошибке, выходим
        if ( ! $path OR $this->has_path($path) )
            return;

        $this->paths[] = $path;
    }

    public function add_url($url)
    {
        // Если такой url уже есть в ошибке, выходим
        if ( ! $url OR $this->has_url($url) )
            return;

        $this->urls[] = $url;
    }

    public function get_message()
    {
        return $this->loaded() ? $this->message : NULL;
    }

    public function get_paths()
    {
        return $this->loaded() ? (array) $this->paths->as_array() : array();
    }

    public function get_urls()
    {
        return $this->loaded() ? (array) $this->urls->as_array() : array();
    }

    public function has_path($path)
    {
        return $this->loaded()
            ? in_array($path, $this->get_paths())
            : NULL;
    }

    public function has_url($url)
    {
        return $this->loaded()
            ? in_array($url, $this->get_urls())
            : NULL;
    }

    public function set_time()
    {
        $this->time = time();
        return $this;
    }

    public function get_time()
    {
        return $this->time ?: parent::get_time();
    }

    /**
     * Сохраняем в модели unix timestamp времени последнего уведомления об ошибке
     * @return $this
     */
    public function set_last_notification_time()
    {
        $this->last_notification_time = time();
        return $this;
    }

    /**
     * Возвращает unix timestamp времени последнего уведомления об ошибке
     * @return int
     */
    public function get_last_notification_time()
    {
        return $this->last_notification_time ?: 0;
    }

    /**
     * Отмечает ошибку как новую и требующую внимания разработчиков
     */
    public function mark_new()
    {
        $this->is_resolved = FALSE;

        // Добавляем запись в историю о новой ошибке
        $this->add_history("New");

        return $this;
    }

    /**
     * Отмечает ошибку как повторяющуюся
     */
    public function mark_repeat()
    {
        // Если ошибка не была исправлена, ничего не делаем
        if ( ! $this->is_resolved() )
            return $this;

        $this->is_resolved = FALSE;

        // Добавляем запись в историю
        $this->add_history("Repeat");

        return $this;
    }

    /**
     * Отмечает ошибку как исправленную
     */
    public function mark_resolved()
    {
        $this->is_resolved = TRUE;

        // Сохраняем информацию о пользователе, который последним исправил проблему (для статистики)
        $this->resolved_by = (int) Env::user()->pk();

        // Добавляем запись в историю
        $this->add_history("Resolved");

        return $this;
    }

    /**
     * Возвращает TRUE, если ошибка исправлена
     * @return mixed
     */
    public function is_resolved()
    {
        return $this->is_resolved;
    }

    /**
     * Возвращает ID пользователя, который последним исправил проблему
     * @return integer
     */
    public function get_resolved_by()
    {
        return $this->resolved_by;
    }

    /**
     * Добавляет запись в историю модификаций ошибки
     * @param string $what Что сделали
     * @return $this
     */
    protected function add_history($what)
    {
        /** @var Model_User $user */
        $user = Env::user(TRUE);

        $item = new stdClass();

        $item->who  = $user ? (int) $user->pk() : NULL;
        $item->when = time();
        $item->what = $what;

        $this->history[] = $item;

        return $this;
    }

    /**
     * Возвращает массив записей в истории
     * @return array
     */
    public function get_history()
    {
        return $this->history;
    }

    /**
     * Возвращает массив объектов с полноценной отформатированной историей
     * @return array
     */
    public function get_formatted_history()
    {
        $history = $this->get_history();

        $result = array();

        foreach ( $history as $item )
        {
            $data = (object) $item;

            $user_id = $data->who;

            if ( $user_id )
            {
                /** @var Model_User $user_orm */
                $user_orm = ORM::factory("User", $user_id);

                $full_name = $user_orm->get_full_name();
                $email = $user_orm->get_email();

                $data->who = $email ? HTML::mailto($email, $full_name) : $full_name;
            }
            else
            {
                $data->who = __("Неизвестно");
            }

            $data->when = date("d.m.Y в H:i:s", $data->when);

            switch ($data->what)
            {
                case "Resolved":
                    $css_class = "label-success";
                    break;

                case "Repeat":
                    $css_class = "label-warning";
                    break;

                default:
                    $css_class = "label-info";
            }

            $data->what = '<span class="label '. $css_class .'">'. $data->what .'</span>';

            $result[] = $data;
        }

        return $result;
    }

    /**
     * Возвращает TRUE, если ошибка нуждается в уведомлении
     * @param $repeat_count
     * @param $repeat_delay
     * @return bool
     */
    public function is_notification_needed($repeat_count, $repeat_delay)
    {
        // Предотвращаем слишком частое уведомление
        if ( ($this->get_time() - $this->get_last_notification_time()) < $repeat_delay )
            return FALSE;

        // Если ошибка была исправлена и сейчас повторно появилась, сообщаем о ней
        if ( $this->is_changed("is_resolved") AND ! $this->is_resolved() )
            return TRUE;

        // Разрешаем отправку уведомления, если ошибка повторяется уже N-ный раз
        return ( ($this->get_counter() % $repeat_count) == 1 );
    }

    public function collection_size($query = array())
    {
        if ( ! $query )
        {
            // Считаем только новые и повторно появившиеся ошибки
            $query = array(
                "is_resolved" => array('$in' => array(NULL, FALSE))
            );
        }

        return parent::collection_size($query);
    }

}