<?php defined('SYSPATH') or die('No direct script access.');

class XMPP_Lib_XMPPHP extends XMPP_Lib {

    protected $config_name = "xmpphp";

    /** @var  XMPPHP_XMPP */
    protected $lib;

    public function connect()
    {
        // Подключение Realplexor
        require Kohana::find_file('vendor', 'XMPPHP/XMPP');

        $this->lib = new XMPPHP_XMPP(
            $this->config['host'],
            $this->config['port'],
            $this->config['username'],
            $this->config['password'],
            $this->config['resource'],
            $this->config['server'],
            $this->config['print_log']
        );

        $this->lib->connect();
        $this->lib->processUntil('session_start');
        $this->lib->presence();
        $this->lib->autoSubscribe();
    }

    public function personal_send($xmpp_id, $text)
    {
        $this->lib->message($xmpp_id, $text);
    }

    public function disconnect()
    {
        $this->lib->disconnect();
    }

}

/*
 * catch(XMPPHP_Exception $e)
 */