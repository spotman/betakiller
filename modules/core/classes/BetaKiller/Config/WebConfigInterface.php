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

    /**
     * Returns array of middlewares` classes which should be executed if no matched route is found
     *
     * @return string[]
     */
    public function getNotFoundPipeline(): array;
}
