<?php

/**
 * @author Spotman denis.terehov@tsap-spb.ru
 *
 * Сервис для автоматизированного сбора ошибок во фронтенде
 */

class Controller_Error_Js extends Controller_Template {

    /**
     * Уведомления будут отсылаться при повторном появлении ошибки в N-ный раз
     */
    const NOTIFICATION_REPEAT_COUNT = 50;

    /**
     * Уведомления будут отсылаться не чаще чем T секунд
     */
    const NOTIFICATION_REPEAT_DELAY = 30;


    public function action_catch()
    {
        $data = $this->request->post("data");

        if ( ! $data )
            throw new HTTP_Exception_500("Нечего обрабатывать");

        try
        {
            // Превращаем строку в массив
            $data = (object) json_decode($data);

            $message = $data->message;

            $url = isset($data->url)
                ? $data->url
                : NULL;

            $filename = isset($data->filename)
                ? $data->filename
                : NULL;

            $line = isset($data->line)
                ? $data->line
                : NULL;

            $path = $filename
                ? "$filename:$line"
                : NULL;

            $stack = isset($data->stacktrace)
                ? (array) $data->stacktrace
                : array();

            $additional = isset($data->additional)
                ? (array) $data->additional
                : NULL;

            // Если текст сообщения не указан, выходим (мы не сможем понять, что произошло и починить эту ошибку)
            if ( ! $message )
            {
                $this->send_json(self::JSON_ERROR);
                return;
            }

            // Вычисляем уникальный хеш сообщения
            $hash = Model_Error_Message_Js::make_hash($message);

            // Маркер новой ошибки (о ней нужно сообщить разработчикам)
            $new_error = FALSE;

            /** @var Model_Error_Message_Js $odm */
            $odm = Mango::factory("Error_Message_Js");

            // Попробуем поискать в базе запись с таким же сообщением об ошибке
            $odm->find_by_hash($hash);

            // Если нет, создаём её
            if ( ! $odm->loaded() )
            {
                $odm->hash= $hash;
                $odm->message = $message;
                $odm->urls = array();
                $odm->paths = array();
                $odm->traces = array();

                // Отмечает ошибку как новую и требующую внимания разработчиков
                $odm->mark_new();

                $odm->create();

                $new_error = TRUE;
            }
            else
            {
                // Отмечаем ошибку как повторяющуюся
                $odm->mark_repeat();
            }

            // Сохраняем время последнего появления ошибки
            $odm->set_time();

            // Добавляем url документа, если его нет в списке
            $odm->add_url($url);

            // Добавляем путь с ошибкой, если его нет в списке
            $odm->add_path($path);

            // Добавляем стектрейс
            $odm->add_trace($stack);

            // Увеличиваем кол-во появлений текущей ошибки
            $odm->increment_counter();

            // Если это свежая ошибка или она повторяется слишком часто
            if ( $new_error OR $odm->is_notification_needed(self::NOTIFICATION_REPEAT_COUNT, self::NOTIFICATION_REPEAT_DELAY) )
            {
                try
                {
                    // Уведомляем о ней разработчиков
                    XMPP::simple(
                        array("denis.terehov@tsap-spb.ru", "ivan.shalin@sentra.ru", "dmitriy.borbotko@tsap-spb.ru"),

                        "\r\n". $message .
                        "\r\n\r\n".

                        "URLs:".
                        "\r\n".
                        implode("\r\n", $odm->get_urls()).

                        ( $odm->get_paths()
                            ? "\r\n\r\n".
                            "Paths:".
                            "\r\n".
                            implode("\r\n", $odm->get_paths())
                            : NULL
                        ).

                        ( $stack
                            ? "\r\n\r\n".
                            implode("\r\n", $stack)
                            : NULL
                        ).

                        ( $additional
                            ? "\r\n\r\n".
                            "Additional:".
                            "\r\n".
                            $this->indent( json_encode($additional) )
                            : NULL
                        )
                    );

                    // Сохраняем время последнего уведомления об ошибке
                    $odm->set_last_notification_time();
                }
                catch ( Exception $e )
                {
                    // Сохраняем ошибку в системном логе
                    Kohana_Exception::log($e);
                }
            }

            // Сохраняем данные
            $odm->update();

            // Формируем ответ
            $this->send_json(self::JSON_SUCCESS);
        }
        catch ( Exception $e )
        {
            //Kohana_Exception::notify($e);
            // Kohana_Exception::log($e);
            $this->send_json(self::JSON_ERROR);
        }
    }


    /**
     * Indents a flat JSON string to make it more human-readable.
     *
     * @param string $json The original JSON string to process.
     *
     * @return string Indented version of the original JSON string.
     * @link http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
     */
    protected function indent($json)
    {
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

                // If this character is the end of an element,
                // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }
}