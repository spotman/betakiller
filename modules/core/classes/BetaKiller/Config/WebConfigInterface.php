<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface WebConfigInterface
{
    /**
     * "middleware class" => "array of dependencies (middleware classes)"
     *
     * @return string[]
     */
    public function getPipeMiddlewares(): array;

    /**
     * @param string $fqcn Middleware class
     *
     * @return string[] array of dependencies (middleware classes) on null if no dependencies defined
     */
    public function getMiddlewareDependencies(string $fqcn): array;

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
     * Returns middleware class which should be executed if no matched route is found
     *
     * @return string
     */
    public function getNotFoundHandler(): string;
}
