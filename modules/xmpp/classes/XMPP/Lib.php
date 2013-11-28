<?php defined('SYSPATH') or die('No direct script access.');

abstract class XMPP_Lib {

    /** @var string имя файла-конфига */
    protected $config_name = NULL;
    protected $config = array();

    abstract public function connect();
    abstract public function disconnect();
    abstract public function personal_send($xmpp_id, $text);

    public function __construct($config_key = NULL)
    {
        if ( ! $config_key ) $config_key = "default";

        // Получаем настройки
        $config_file = Kohana::$config->load($this->config_name);
        $this->config += $config_file->$config_key;

        // Соединяемся с сервером
        $this->connect();
    }

    public function send(XMPP_Message $msg)
    {
        // Извлекаем получателя сообщения
        $target = $msg->get_target();

        // Извлекаем текст сообщения
        $text = $msg->get_text();

        // Множественная отправка
        if ( is_array($target) )
        {
            foreach ( $target as $user_mail )
            {
                $this->personal_send($user_mail, $text);
            }
        }
        // Персональная отправка
        else
        {
            $this->personal_send($target, $text);
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }

}