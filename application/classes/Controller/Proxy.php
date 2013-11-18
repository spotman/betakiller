<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Controller_Proxy
 * Allows to proxy system calls to another class
 */
abstract class Controller_Proxy extends Kohana_Controller {

    protected $_proxy_object;
    protected $_proxy_method;
    protected $_proxy_result;

    final public function execute()
    {
        $this->_init();

        // Determine the method to use
        $this->_proxy_method = $this->get_proxy_method();

        // Getting the proxy object
        $this->_proxy_object = $this->get_proxy_object();

        // If the action doesn't exist, it's a 404
        if ( ! method_exists($this->_proxy_object, $this->_proxy_method) )
        {
            throw new HTTP_Exception_404('Can not find method [:method] in proxy class [:class]',
                array(':class' => get_class($this->_proxy_object), ':method' => $this->_proxy_method)
            );
        }

        // Adding response to stack
        Response::push($this->response, $this->request);

        try
        {
            $this->_execute();
        }
        catch ( Kohana_Exception $e )
        {
            Response::handle_exception($e);
        }

        // Return the response
        return Response::pop();
    }

    protected function _init()
    {
        // Empty by default
    }

    protected function _execute()
    {
        // Controller "before" method
        $this->before();

        // Proxy request
        $this->_proxy_result = $this->_execute_proxy();

        // Controller "after" method
        $this->after();
    }

    protected function _execute_proxy()
    {
        // Execute the action itself
        return $this->_proxy_object->{$this->_proxy_method}($this);
    }

    protected function get_proxy_object()
    {
        return $this;
    }

    /**
     * @return string
     */
    protected function get_proxy_method()
    {
        return 'action_'.$this->request->action();
    }

}