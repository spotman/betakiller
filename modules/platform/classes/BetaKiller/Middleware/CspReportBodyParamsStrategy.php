<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use Mezzio\Helper\BodyParams\JsonStrategy;

class CspReportBodyParamsStrategy extends JsonStrategy
{
    public function match(string $contentType) : bool
    {
        return $contentType === 'application/csp-report';
    }
}
