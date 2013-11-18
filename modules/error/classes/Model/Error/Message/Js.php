<?php

class Model_Error_Message_Js extends Model_Error_Message_Base {

    protected $_collection = "jsErrors";

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

        // Ссылки на документы, в которых произошли ошибки
        'urls'          => array(
            'type'      => 'array',
        ),

        // Ссылки на js-файлы, в которых произошли ошибки + номера строк с ошибкой (filename:line)
        'paths'          => array(
            'type'       => 'array',
        ),

        // Стектрейсы текущей ошибки
        'traces'          => array(
            'type'       => 'array',
        ),

        // Общее кол-во появлений текущей ошибки
        'total'          => array(
            'type'       => 'counter',
        ),

        // Отметка о том, что ошибка исправлена
        'is_resolved'    => array(
            'type'       => 'boolean',
        ),

        // ID пользователя, который последним исправил проблему
        'resolved_by'    => array(
            'type'      => 'integer'
        ),

        // История операций над ошибкой
        'history'        => array(
            'type'       => 'array',
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

    public function get_traces()
    {
        return $this->loaded() ? (array) $this->traces->as_array() : array();
    }

    public function add_trace($trace)
    {
        if ( ! $trace )
            return;

        $this->traces[] = $trace;
    }
}