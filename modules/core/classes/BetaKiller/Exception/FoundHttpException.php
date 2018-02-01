<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class FoundHttpException extends \HTTP_Exception_302
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
