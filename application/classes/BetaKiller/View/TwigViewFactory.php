<?php
namespace BetaKiller\View;


class TwigViewFactory implements ViewFactoryInterface
{
    public function create(string $file, ?array $data = null): ViewInterface
    {
        return \Twig::factory($file, $data ?: []);
    }
}
