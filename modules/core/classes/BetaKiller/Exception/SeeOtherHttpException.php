<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class SeeOtherHttpException extends \HTTP_Exception_303
{
    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        parent::__construct();

        $this->location($url);
    }
}
