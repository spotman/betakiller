<?php

class Request extends BetaKiller\Utils\Kohana\Request
{
    /**
     * @return array
     */
    public function getArgumentsGet(): array
    {
        return parent::query();
    }


    /**
     * @return array
     */
    public function getArgumentsPost(): array
    {
        return parent::post();
    }


    /**
     * @return array
     */
    public function getArgumentsRequest(): array
    {
        return $_REQUEST;
    }
}
