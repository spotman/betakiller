<?php
namespace BetaKiller\Helper;

use Request;

class RequestHelper
{
    private $request;

    /**
     * RequestHelper constructor.
     */
    public function __construct()
    {
        $this->request = Request::current();
    }

    public function getCurrentUrl(): ?string
    {
        return $this->request ? $this->request->uri() : null;

    }

    public function getUrlQueryParts(): ?array
    {
        return $this->request ? $this->request->query() : null;
    }
}
