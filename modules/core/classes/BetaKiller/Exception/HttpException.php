<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

use BetaKiller\Exception;

class HttpException extends Exception implements HttpExceptionInterface
{
    /**
     * @param   integer   $code the exception code
     * @param string $message
     * @param array  $variables
     */
    public function __construct(int $code, string $message = null, array $variables = null)
    {
        parent::__construct($message, $variables, $code);
    }
}
