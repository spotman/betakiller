<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Tests;

use BetaKiller\IFace\AbstractIFace;

class TestsDummy extends AbstractIFace
{
    /**
     * @return string[]
     */
    public function getData(): array
    {
        return [];
    }
}
