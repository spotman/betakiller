<?php defined('SYSPATH') or die('No direct script access.');

class XMPP_Message_Simple extends XMPP_Message {

    protected $text;

    public function set_text($text)
    {
        $this->text = $text;
    }

    public function get_text()
    {
        return $this->text;
    }

}