<?php
namespace BetaKiller\DI;

trait Mixin
{
    use Mixin\Base;

    protected function getCurrentUser()
    {
        return $this->getContainer()
    }
}
