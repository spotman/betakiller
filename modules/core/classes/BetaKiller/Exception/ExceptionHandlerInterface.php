<?php
namespace BetaKiller\Exception;


interface ExceptionHandlerInterface
{
    public function handle(\Throwable $e): \Response;
}
