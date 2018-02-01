<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class PermanentRedirectHttpException extends \HTTP_Exception_301
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
