<?php ( defined('SYSPATH') ) OR die('No direct script access.');


class CRM_Import_Abstract_Actualize_Errors extends CRM_Import_Abstract_Errors {

    /*
     * константы типов ошибок
     */

    /** ошибка "Номер отсутствует в базе" */
    const ERROR_PHONE_IS_MISSING = 1;

    /** ошибка "Перевод заявки в статус /Счёт активирован/ заблокирован" */
    const ERROR_STATUS_WORKFLOW_CHECK_FAILED = 2;

    /** ошибка "Системная ошибка при сохранении заявки" */
    const ERROR_ORDER_SAVING_FAILED = 3;

    /** ошибка "Системная ошибка при сохранении заявки" */
    const ERROR_ORDER_SAVING_EXCEPTION = 4;

    /** ошибка "Неизвестный статус" */
    const ERROR_UNKNOWN_STATUS = 5;

    /** ошибка "Неизвестный статус" */
    const ERROR_INCORRECT_DATE_FORMAT = 6;

    /** ошибка "Монтажник не найден" */
    const ERROR_MOUNTER_NOT_FOUND = 7;

    /**
     * массив сообщений об ошибках
     * @var array
     */
    public $error_msg = array(
        self::ERROR_DOC_EXCEPTION                   => '',
        self::ERROR_PHONE_IS_MISSING                => 'номер отсутствует в базе',
        self::ERROR_ORDER_SAVING_FAILED             => 'cистемная ошибка при сохранении заявки',
        self::ERROR_STATUS_WORKFLOW_CHECK_FAILED    => 'нельзя перевести заявку ',
        self::ERROR_ORDER_SAVING_EXCEPTION          => '<strong style="color:red">заявка заполнена неправильно или неполностью</strong>',
        self::ERROR_UNKNOWN_STATUS                  => 'неизвестный статус',
        self::ERROR_INCORRECT_DATE_FORMAT           => 'неверный формат даты',
        self::ERROR_MOUNTER_NOT_FOUND               => 'монтажник не найден',
    );

    /**
     * @param string $number номер телефона из заявки
     * @param int $error_type тип ошибки (константа ERROR_...)
     * @param string $msg опциональное сообщение об ошибке, которое будет дописано к основному
     */
    public function log_error($number, $error_type, $msg = NULL)
    {
        $index = (string) $number;
        $this->e[ $index ] = $this->error_msg[ $error_type ].( $msg ? (' '.$msg) : '');
    }

}