<?php
namespace BetaKiller\Helper;


class ResponseHelper
{
    /**
     * @param string $url
     */
    public function redirect(string $url): void
    {
        \HTTP::redirect($url);
    }
}
