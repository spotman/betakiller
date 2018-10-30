<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Url\AbstractUrlElement;

abstract class AbstractAction extends AbstractUrlElement implements ActionInterface
{
    /**
     * @return string
     */
    public static function getSuffix(): string
    {
        return self::SUFFIX;
    }
}
