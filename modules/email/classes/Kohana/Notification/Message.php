<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_Notification_Message {

    use Util_Factory_Simple;

    /**
     * @var Notification_User_Interface
     */
    protected $_from;

    /**
     * @var Notification_User_Interface[]
     */
    protected $_to = array();

    /**
     * @var string
     */
    protected $_subj;

//    /**
//     * @var string
//     */
//    protected $_text;

    /**
     * Template codename
     *
     * @var string
     */
    protected $_template_name;

    /**
     * Key => value bindings for template
     *
     * @var array
     */
    protected $_template_data;

    /**
     * @return Notification_User_Interface
     */
    public function get_from()
    {
        return $this->_from;
    }

    /**
     * @param Notification_User_Interface $value
     * @return $this
     */
    public function set_from(Notification_User_Interface $value)
    {
        $this->_from = $value;
        return $this;
    }

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

//    public function get_text()
//    {
//        return $this->_text;
//    }

//    /**
//     * @param string $value
//     * @return $this
//     */
//    public function set_text($value)
//    {
//        $this->_text = $value;
//        return $this;
//    }

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

    public function set_template_name($template_name)
    {
        $this->_template_name = $template_name;
        return $this;
    }

    public function set_template_data(array $data)
    {
        $this->_template_data = $data;
        return $this;
    }

    protected function template_factory()
    {
        return View::factory();
    }

    protected function get_template_path()
    {
        return 'templates'.DIRECTORY_SEPARATOR.'notification';
    }

    public function render($transport_name)
    {
        $view = $this->template_factory();

        $data = array_merge($this->_template_data, array(
            'to'        =>  $this->_to,
            'subject'   =>  $this->_subj,
        ));

        $view->set($data);

        return $view->render(
            $this->get_template_path().DIRECTORY_SEPARATOR.$this->_template_name.'-'.$transport_name
        );
    }

}
