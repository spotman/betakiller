<?php
namespace BetaKiller\View;

interface ViewFactoryInterface
{
    public function create(string $file): ViewInterface;

    /**
     * Returns true if template exists
     *
     * @param string $file
     *
     * @return bool
     */
    public function exists(string $file): bool;
}
