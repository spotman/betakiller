<?php
namespace BetaKiller\View;


interface ViewFactoryInterface
{
    public function create(string $file, ?array $data = null): ViewInterface;
}
