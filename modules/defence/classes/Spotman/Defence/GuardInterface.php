<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface GuardInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array;
}
