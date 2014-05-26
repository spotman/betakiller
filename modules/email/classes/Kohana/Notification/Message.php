<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_Notification_Message {

    use Util_Factory_Simple;

//    /**
//     * @var Notification_User_Interface
//     */
//    protected $_from;

    /**
     * @var Notification_User_Interface[]
     */
    protected $_to = array();

    /**
     * @var string
     */
    protected $_subj;

    /**
     * @var string
     */
    protected $_text;

//    /**
//     * @return Notification_User_Interface
//     */
//    public function get_from()
//    {
//        return $this->_from;
//    }
//
//    /**
//     * @param Notification_User_Interface $value
//     * @return $this
//     */
//    public function set_from(Notification_User_Interface $value)
//    {
//        $this->_from = $value;
//        return $this;
//    }

    /**
     * @return Notification_User_Interface[]
     */
    public function get_to()
    {
        return $this->_to;
    }

    /**
     * @param Notification_User_Interface $value
     * @return $this
     */
    public function set_to(Notification_User_Interface $value)
    {
        $this->_to[] = $value;
        return $this;
    }

    public function get_subj()
    {
        return $this->_subj;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function set_subj($value)
    {
        $this->_subj = $value;
        return $this;
    }

    public function get_text()
    {
        return $this->_text;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function set_text($value)
    {
        $this->_text = $value;
        return $this;
    }

//    /**
//     * @param $email
//     * @return $this
//     */
//    public function from_email($email)
//    {
//        $from = Notification_User_Email::factory($email);
//
//        return $this->set_from($from);
//    }

    public function send()
    {
        Notification::instance()->send($this);
    }

}