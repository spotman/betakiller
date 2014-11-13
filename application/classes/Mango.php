<?php defined('SYSPATH') OR die('No direct access allowed.');

class Mango extends Kohana_Mango {

    /**
     * Возвращает количество элементов в коллекции, представленной текущей моделью
     * @param array $query Параметры выборки
     * @return mixed
     */
    public function collection_size($query = array())
    {
        return $this->db()->count($this->_collection, $query);
    }

}
