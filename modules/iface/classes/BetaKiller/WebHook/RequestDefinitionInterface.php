<?php
declare(strict_types=1);

namespace BetaKiller\WebHook;

interface RequestDefinitionInterface
{
    /**
     * Returns HTTP method
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return array [string "name" => string "value"]
     */
    public function getFields(): array;
}
