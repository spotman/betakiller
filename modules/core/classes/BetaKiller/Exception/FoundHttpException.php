<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class FoundHttpException extends RedirectException
{
    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        parent::__construct(302, $url);
    }
}
