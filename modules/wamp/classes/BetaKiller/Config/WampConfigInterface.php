<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface WampConfigInterface
{
    /**
     * @return string
     */
    public function getNamespace(): string;

    /**
     * @return string
     */
    public function getConnectionHost(): string;

    /**
     * @return string
     */
    public function getConnectionPort(): string;
}
