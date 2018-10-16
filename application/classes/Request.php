<?php

class Request extends \Kohana_Request
{
    /**
     * @return mixed
     * @deprecated
     */
    public function module()
    {
        return $this->param('module');
    }

    /**
     * @return string
     * @deprecated
     */
    public function getClientIp(): string
    {
        return static::$client_ip;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getUserAgent(): string
    {
        return static::$user_agent;
    }
}
