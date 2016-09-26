<?php

class Model_Error_Message_Php extends Model_Error_Message_Base {

    protected $_collection = "phpErrors";

    protected $_fields = array(

        // Хеш, по которому проверяется уникальность сообщения
        'hash'          => array(
            'type'      => 'string'
        ),

        // Время последнего появления ошибки
        'time'          => array(
            'type'      => 'integer'
        ),

        // Время последнего уведомления об ошибке
        'last_notification_time'    => array(
            'type'      => 'integer'
        ),

        // Собственно сообщение об ошибке
        'message'       => array(
            'type'      => 'string'
        ),

        // Имя модуля, в котором произошла ошибка
        'module'        => array(
            'type'      => 'string'
        ),

        // Ссылки на документы, в которых произошли ошибки
        'urls'          => array(
            'type'      => 'array',
        ),

        // Ссылки на php-файлы, в которых произошли ошибки + номера строк с ошибкой (filename:line)
        'paths'         => array(
            'type'      => 'array',
        ),

        // Общее кол-во появлений текущей ошибки
        'total'         => array(
            'type'      => 'counter',
        ),

        // Отметка о том, что ошибка исправлена
        'is_resolved'   => array(
            'type'      => 'boolean',
        ),

        // ID пользователя, который последним исправил проблему
        'resolved_by'   => array(
            'type'      => 'integer'
        ),

        // История операций над ошибкой
        'history'       => array(
            'type'      => 'array',
        ),


        // the attributes below might seem strange - they are :-)
        // but in the demos I needed a counter, a set, an array and an
        // multidimensional array of counters
        // 'some_counter' => array('type'=>'counter'),
        // 'categories'   => array('type'=>'set'),
        // 'some_array'   => array('type'=>'array'),
        // 'report'       => array('type'=>'array','type_hint'=>'counter'),
        // 'local_data'   => array('type'=>'string','local'=>TRUE)
    );

    public function get_id()
    {
        return (string) $this->_id;
    }

    public function add_trace(Response $r)
    {
        $filename = $this->get_trace_full_path();

        // Помещаем рендер стектрейса в файл
        file_put_contents($filename, (string) $r);
    }

    public function get_trace()
    {
        return @ file_get_contents( $this->get_trace_full_path() );
    }

    public function get_trace_full_path()
    {
        $dir = MODPATH ."error/media/php_traces";

        if ( ! file_exists($dir) )
        {
            mkdir($dir, 0664, TRUE);
        }

        return $dir .DIRECTORY_SEPARATOR. $this->get_id();
    }

    public function add_module($module)
    {
        $this->module = $module;
    }

    public function get_module()
    {
        return $this->module;
    }

    public function delete($safe = TRUE)
    {
        // Удаляем файл со стектрейсом
        @ unlink( $this->get_trace_full_path() );

        // Удаляем запись из базы
        parent::delete($safe);
    }
}
