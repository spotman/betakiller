<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class SeeOtherHttpException extends RedirectException
{
    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        parent::__construct(303, $url);
    }
}
