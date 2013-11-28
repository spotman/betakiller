<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class XMPP - FACADE for interacting via XMPP protocol
 *
 *
 * Usage:
 *
 * (optional config selection) XMPP::init("<config_key>");
 *
 * XMPP::simple("<target_xmpp_id>", "<message_text>");
 *
 * OR
 *
 * Step 1:
 *
 * Create class XMPP_Message_<message_type> for new type of message or use existing class
 *
 * Step 2:
 *
 * $msg = XMPP::message("<message type>");
 * $msg->set_target("anyone@gmail.com");
 * $msg->send();
 *
 */

class XMPP {

    const LIB_NAME = "XMPPHP";

    /** @var  XMPP */
    static protected $_instance = NULL;

    /** @var  XMPP_Lib */
    protected $lib;

    /**
     * @static
     * @param string $config_key
     * @return XMPP
     */
    static public function instance($config_key = NULL)
    {
        if(self::$_instance === NULL)
        {
            self::$_instance = new self;
            self::$_instance->lib_factory($config_key);
        }

        return self::$_instance;
    }

    /**
     * @param string $type
     * @return XMPP_Message|XMPP_Message_Simple
     * @throws Exception
     */
    static public function message($type = "Simple")
    {
        $class = 'XMPP_Message_'. ucfirst($type);

        if ( ! class_exists($class) )
            throw new Exception("Неизвестный тип XMPP сообщения; Не найден класс ". $class);

        return new $class( self::instance()->lib );
    }

    static public function simple($target, $message)
    {
        /** @var  $msg XMPP_Message_Simple */
        $msg = self::message("Simple");
        $msg->set_target($target);
        $msg->set_text($message);
        $msg->send();
    }

    protected function lib_factory($config_key = NULL)
    {
        $class = 'XMPP_Lib_'. ucfirst(self::LIB_NAME);

        if ( ! class_exists($class) )
            throw new Exception("Неизвестная XMPP библиотека; Не найден класс ". $class);

        $this->lib = new $class($config_key);
    }

    private function __wakeup() {}
    private function __construct() {}
    private function __clone() {}

}