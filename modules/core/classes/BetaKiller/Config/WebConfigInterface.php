<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface WebConfigInterface
{
    /**
     * "middleware class" => "array of dependencies (middleware classes)"
     *
     * @return array[]
     */
    public function getMiddlewares(): array;

    /**
     * "pattern" => "middleware class"
     *
     * @return string[]
     */
    public function fetchGetRoutes(): array;

    /**
     * "pattern" => "middleware class"
     *
     * @return string[]
     */
    public function fetchPostRoutes(): array;

    /**
     * "pattern" => "middleware class"
     *
     * @return string[]
     */
    public function fetchAnyRoutes(): array;
}
