<?php


abstract class CRM_Import_Abstract_Sheet extends Import_Excel_Sheet {

    /** @var CRM_Import_Abstract_Errors объект - хранилище ошибок, возникающих при обработке заявки */
    protected $e;

    /** @var CRM_Status объект, управляющий статусами заявки */
    protected $s;


    public function __construct()
    {
        $this->s = Env::get('status', 'crm');
    }

    abstract public function set_e(CRM_Import_Abstract_Errors $errors);

    /**
     * Возвращает ORM-модель заявки дял текущего проекта
     * @param $orm_pk int значение первичного ключа (для поиска заявки по нему)
     * @return Model_Order|StdClass
     */
    protected function get_order_model($orm_pk = null)
    {
        return CRM::factory('order', $orm_pk);
    }

    /**
     * производит поиск заявок в текущем проекте
     * @param string $phone
     * @return Model_Order|Database_Result
     */
    protected function find_orders_by_phone($phone)
    {
        $orders = $this->get_order_model();

        return $orders
            ->where('project_id', '=', Env::get("project", 'crm')->id)
            ->and_where('phone_home', '=', $phone)
            ->find_all();
    }


    /**
     * проверяет можно ли перевести заявку из статуса в статус
     * @param int $status_id_from
     * @param int $status_id_to
     * @param string $role глобальная роль, по которой следует выбрать workflow
     * @return bool
     */
    protected function workflow_check($status_id_from, $status_id_to, $role = NULL)
    {
        return $this->s->can_move_into_status($status_id_from, $status_id_to, $role);
    }


    /**
     * возвращает ID статуса по его кодовому имени (READY, REVISIT, etc)
     * @param string $name
     * @return bool
     */
    protected function get_status_by_name($name)
    {
        return $this->s->by_name($name);
    }


    /**
     * хелпер к логированию ошибок
     * @param string $number номер телефона
     * @param int $error_type тип ошибки (константа из CRM_Import_Strela_Errors)
     * @param string $msg опциональное сообщение об ошибке
     */
    protected function log_error($number, $error_type, $msg = null)
    {
        $this->e->log_error($number, $error_type, $msg);
    }


    abstract protected function debug_order($order, $die = false);
}

abstract class CRM_Import_Abstract_Sheet_Exception extends Import_Excel_Sheet_Exception {}