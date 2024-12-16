<?php

declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Url\AbstractUrlElementInstance;

abstract readonly class AbstractAction extends AbstractUrlElementInstance implements ActionInterface
{
    /**
     * @return string
     */
    public static function getSuffix(): string
    {
        return self::SUFFIX;
    }
}
