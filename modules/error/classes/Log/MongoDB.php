<?php defined('SYSPATH') OR die('No direct script access.');

class Log_MongoDB extends Log_Writer {

    /**
     * Адрес электронной почты, на который отправляются уведомления об ошибках
     */
    const NOTIFICATION_EMAIL = "i.am@spotman.ru";

    /**
     * Уведомления будут отсылаться при повторном появлении ошибки в N-ный раз
     */
    const NOTIFICATION_REPEAT_COUNT = 50;

    /**
     * Уведомления будут отсылаться не чаще чем T секунд
     */
    const NOTIFICATION_REPEAT_DELAY = 30;


    /**
     * Write an array of messages.
     *
     *     $writer->write($messages);
     *
     * @param   array $messages
     * @return  void
     */
    public function write(array $messages)
    {
        foreach ($messages as $message)
        {
            try
            {
                // Write each message into the log
                $this->write_message($message);
            }
            catch ( Exception $e )
            {
                // Prevent logging recursion
                Kohana::$log->detach($this);

                // Store error in other log writers
                Kohana_Exception::log($e);

                // Stop executing
                break;
            }
        }
    }

    protected function write_message(array $msg)
    {
        /** @var Exception|Kohana_Exception|NULL $exception */
        $exception = isset($msg['additional']['exception'])
            ? $msg['additional']['exception']
            : NULL;

        if ( $exception AND ($exception instanceof Kohana_Exception) AND ! $exception->is_notification_enabled() )
            return;

//        $time = $msg['time'];
//        $level = $this->_log_levels[$msg['level']];

        /** @var Model_Error_Message_Php $odm */
        $odm = Mango::factory("Error_Message_Php");

        if ( $exception )
        {
            $class = get_class($exception);
            $code = $exception->getCode();
            $file = $exception->getFile();
            $line = $exception->getLine();

            // Собираем сообщение и экранируем спецсимволы, чтобы минимизировать XSS
            $message = HTML::chars("$class [$code]: ". $exception->getMessage());
        }
        else
        {
            $file = $msg['file'];
            $line = $msg['line'];

            $message = $msg['body'];
        }

        // Вычисляем уникальный хеш сообщения
        $hash = $odm::make_hash($message);

        // Попробуем поискать в базе запись с таким же сообщением об ошибке
        $odm->find_by_hash($hash);

        // Если ошибка найдена
        if ( $odm->loaded() )
        {
            // Отмечаем ошибку как повторяющуюся
            $odm->mark_repeat();
        }
        // Если нет, создаём её
        else
        {
            $odm
                ->set_hash($hash)
                ->set_message($message)
                ->set_urls()
                ->set_paths()
                ->create()

                // Отмечает ошибку как новую и требующую внимания разработчиков
                ->mark_new();
        }

        // Устанавливаем время последнего появления ошибки
        $odm->set_time();

        // Пробуем получить текущий uri
        $url = Request::current() ? Request::current()->detect_uri() : NULL;

        // Добавляем url документа, если его нет в списке
        $odm->add_url($url);

        // Добавляем путь с ошибкой, если его нет в списке
        $odm->add_path($file.":".$line);

        if ( $exception )
        {
            // Получаем дефолтный трейс
            $e_response = Kohana_Exception::response($exception);

            // Добавляем стектрейс
            $odm->add_trace($e_response);
        }

        $module = Request::current() ? Request::current()->module() : NULL;

        // Добавляем имя модуля, чтобы потом группировать исключения
        $odm->add_module($module);

        // Увеличиваем кол-во появлений текущей ошибки
        $odm->increment_counter();

        // Если это свежая ошибка или она повторяется
        if ( $odm->is_notification_needed(static::NOTIFICATION_REPEAT_COUNT, static::NOTIFICATION_REPEAT_DELAY) )
        {
            // Уведомляем разработчиков
            Email::send(
                NULL,
                static::NOTIFICATION_EMAIL,
                "Kohana exception",
                $this->make_email($odm),
                TRUE // is html
            );

            // Сохраняем время последнего уведомления об ошибке
            $odm->set_last_notification_time();
        }

        // Сохраняем данные
        $odm->update();
    }

    protected function make_email(Model_Error_Message_Php $model)
    {
        $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];

        ob_start();
        ?>
        <html>
        <head>
        </head>

        <body>
        <p>Новое исключение: <strong><?= $model->get_message() ?></strong></p>

        <p>
            Встречается по следующим адресам:

        <ul>
            <? foreach ( $model->get_urls() as $url ): ?>
                <li><a href="http://<?= $host . $url ?>"><?= $host . $url ?></a></li>
            <? endforeach ?>
        </ul><br />

        в следующих файлах:

        <ul>
            <? foreach ( $model->get_paths() as $path ): ?>
                <li><?= $path ?></li>
            <? endforeach ?>
        </ul><br />
        </p>

        <p><a href="http://<?= $host ?>/errors/php/<?= $model->get_hash() ?>"><strong>Стектрейс и другая информация здесь</strong></a></p>
        </body>
        </html>
        <?
        return ob_get_clean();
    }

}
