<?php

use BetaKiller\Exception\NotFoundHttpException;

/**
 * Class ControllerProxy
 * Allows to proxy system calls to another class
 */
abstract class ControllerProxy extends Kohana_Controller
{
    protected $proxyObject;

    /**
     * @var string
     */
    protected $proxyMethod;

    /**
     * @var mixed
     */
    protected $proxyResult;

    /**
     * @return \Response
     * @throws \HTTP_Exception_Redirect
     * @throws \Kohana_Exception
     */
    final public function execute(): \Response
    {
        // Determine the method to use
        $this->proxyMethod = $this->getProxyMethod();

        // Getting the proxy object
        $this->proxyObject = $this->getProxyObject();

        // If the action doesn't exist, it's a 404
        if (!method_exists($this->proxyObject, $this->proxyMethod)) {
            throw new NotFoundHttpException('Can not find method [:method] in proxy class [:class]', [
                ':class'  => get_class($this->proxyObject),
                ':method' => $this->proxyMethod,
            ]);
        }

        // Adding response to stack
        Response::push($this->response, $this->request);

        try {
            // Controller "before" method
            $this->before();

            // Proxy request
            $this->proxyResult = $this->executeProxy();

            // Controller "after" method
            $this->after();
        } catch (Throwable $e) {
            Response::handle_exception($e);
        }

        // Return the response
        return Response::pop();
    }

    protected function executeProxy()
    {
        // Execute the action itself
        return $this->proxyObject->{$this->proxyMethod}($this);
    }

    protected function getProxyObject()
    {
        return $this;
    }

    /**
     * @return string
     */
    protected function getProxyMethod(): string
    {
        return 'action_'.$this->request->action();
    }
}
