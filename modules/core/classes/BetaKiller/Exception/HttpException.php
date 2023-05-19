<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

use BetaKiller\Exception;

class HttpException extends Exception implements HttpExceptionInterface
{
    /**
     * @param integer         $code the exception code
     * @param string|null     $message
     * @param array|null      $variables
     * @param \Throwable|null $previous
     */
    public function __construct(int $code, string $message = null, array $variables = null, \Throwable $previous = null)
    {
        parent::__construct($message ?? 'HTTP error', $variables, $code, $previous);
    }
}
