<?php defined('SYSPATH') or die('No direct script access.');

abstract class XMPP_Message {

    /** @var XMPP_Lib */
    protected $xmpp_lib;

    /**
     * либо персональная отправка           - "<xmpp_id>"
     * либо несколько получателей           - array("<xmpp_id>", "<xmpp_id>")
     *
     * @var string|array получатель или получатели сообщения
     */
    protected $target;

    abstract public function get_text();

    public function __construct(XMPP_Lib $xmpp_lib)
    {
        $this->xmpp_lib = $xmpp_lib;
    }

    /**
     * хелпер для отправки сообщения
     */
    final public function send()
    {
        $this->xmpp_lib->send($this);
    }

    public function set_target($target)
    {
        $this->target = $target;
    }

    public function get_target()
    {
        return $this->target;
    }

}