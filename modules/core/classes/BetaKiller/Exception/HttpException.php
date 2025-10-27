<?php

declare(strict_types=1);

namespace BetaKiller\Exception;

use BetaKiller\Exception;
use Throwable;

class HttpException extends Exception implements HttpExceptionInterface
{
    /**
     * @param int             $code the exception code
     * @param string|null     $message
     * @param array|null      $variables
     * @param \Throwable|null $previous
     */
    public function __construct(int $code, string $message = null, array $variables = null, Throwable $previous = null)
    {
        parent::__construct($message ?? '', $variables, $code, $previous);
    }

    public function isInformational(): bool
    {
        return $this->isCodeInRange(100, 199);
    }

    public function isSuccessful(): bool
    {
        return $this->isCodeInRange(200, 299);
    }

    public function isRedirection(): bool
    {
        return $this->isCodeInRange(300, 399);
    }

    public function isClientError(): bool
    {
        return $this->isCodeInRange(400, 499);
    }

    public function isServerError(): bool
    {
        return $this->isCodeInRange(500, 599);
    }

    private function isCodeInRange(int $from, int $to): bool
    {
        return $this->code >= $from && $this->code <= $to;
    }
}
