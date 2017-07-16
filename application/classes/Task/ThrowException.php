<?php

class Task_ThrowException extends \BetaKiller\Task\AbstractTask
{
    protected function _execute(array $params): void
    {
        throw new HTTP_Exception_500('Test CLI exceptions handling');
    }
}
