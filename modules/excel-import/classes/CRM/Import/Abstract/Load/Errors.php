<?php ( defined('SYSPATH') ) OR die('No direct script access.');


class CRM_Import_Abstract_Load_Errors extends CRM_Import_Abstract_Errors{

    /** неверный формат строки */
    const ERROR_INCORRECT_STRING_FORMAT = 8;
    /** неверный формат адреса */
    const ERROR_INCORRECT_ADDRESS_FORMAT = 9;
    /** не предусмотрено значение времени */
    const ERROR_TIME_NOT_AVAILABLE = 10;
    /** тип подключения не опознан */
    const ERROR_CONNECTION_TYPE_UNDEFINED = 11;
    /** сервис не найден */
    const ERROR_SERVUCE_NOT_FOUND = 12;
    /** значение ip-адреса не опознано */
    const ERROR_IP_DEFINITION = 13;
    /** ФИО некорректно */
    const ERROR_FIO_INCORRECT = 14;
    /** город не найден */
    const ERROR_CITY_NOT_FOUND = 15;
    /** адрес не найден */
    const ERROR_ADDRESS_NOT_FOUND = 16;
    /** ФИО пустое */
    const ERROR_FIO_EMPTY = 17;
    /** не предусмотренное значение времени */
    const ERROR_TIME_UNDEFINED = 18;
    /** заявка с таким номером уже есть */
    const ERROR_ORDER_EXISTS = 19;
    /** тариф не найден */
    const ERROR_RATE_NOT_FOUND = 20;

    /**
     * массив сообщений об ошибках
     * @var array
     */
    public $error_msg = array(
        self::ERROR_DOC_EXCEPTION               => '',
        self::ERROR_INCORRECT_STRING_FORMAT     => 'Неверный формат строки',
        self::ERROR_INCORRECT_ADDRESS_FORMAT    => 'Неверный формат адреса',
        self::ERROR_TIME_NOT_AVAILABLE          => 'cистемная ошибка при сохранении заявки',
        self::ERROR_CONNECTION_TYPE_UNDEFINED   => 'Тип подключения не опознан',
        self::ERROR_SERVUCE_NOT_FOUND           => 'Сервис не найден',
        self::ERROR_IP_DEFINITION               => 'Значение ip-адреса не опознано',
        self::ERROR_FIO_INCORRECT               => 'ФИО некорректно',
        self::ERROR_CITY_NOT_FOUND              => 'Город не найден',
        self::ERROR_ADDRESS_NOT_FOUND           => 'Адрес не найден',
        self::ERROR_FIO_EMPTY                   => 'ФИО пустое',
        self::ERROR_ORDER_EXISTS                => 'Заявка с таким номером уже есть',
        self::ERROR_RATE_NOT_FOUND              => 'Тариф не найден',
    );

    /**
     * @param string $number номер телефона из заявки
     * @param int $error_type тип ошибки (константа ERROR_...)
     * @param string $msg опциональное сообщение об ошибке, которое будет дописано к основному
     */
    public function log_error($number, $error_type, $msg = NULL)
    {
        $this->e[ $number ][] = $this->error_msg[ $error_type ].( $msg ? ': '.$msg : '');
    }

}