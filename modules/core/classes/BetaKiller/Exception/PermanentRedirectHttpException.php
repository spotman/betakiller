<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class PermanentRedirectHttpException extends RedirectException
{
    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        parent::__construct(301, $url);
    }
}
