<?php
namespace BetaKiller\Helper;


class ResponseHelper
{
    /**
     * @param string $url
     * @throws \HTTP_Exception_302
     */
    public function redirect(string $url): void
    {
        \HTTP::redirect($url);
    }
}
