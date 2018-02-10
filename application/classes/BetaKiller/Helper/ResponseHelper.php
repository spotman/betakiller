<?php
namespace BetaKiller\Helper;


use BetaKiller\Exception\FoundHttpException;

class ResponseHelper
{
    /**
     * @param string $url
     *
     * @throws \BetaKiller\Exception\FoundHttpException
     */
    public function redirect(string $url): void
    {
        throw new FoundHttpException($url);
    }
}
