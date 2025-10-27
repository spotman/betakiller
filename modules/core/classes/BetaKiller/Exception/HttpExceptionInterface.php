<?php

namespace BetaKiller\Exception;

use BetaKiller\ExceptionInterface;

interface HttpExceptionInterface extends ExceptionInterface
{
    public function isInformational(): bool;

    public function isSuccessful(): bool;

    public function isRedirection(): bool;

    public function isClientError(): bool;

    public function isServerError(): bool;
}
