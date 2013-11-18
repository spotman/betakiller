<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Controller_Developer
 * Базовый класс для создания интерфейсов, доступных только разработчикам системы
 */
class Controller_Developer extends Controller_Template {

    public function before()
    {
        parent::before();

        // Если пользователь не разработчик, выбрасываем исключение с дефолтным текстом
        if ( ! Env::user()->is_developer() )
            throw new HTTP_Exception_403();
    }

}