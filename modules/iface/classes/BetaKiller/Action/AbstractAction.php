<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Url\AbstractUrlElement;
use Spotman\Defence\DefinitionBuilder;
use Spotman\Defence\DefinitionBuilderInterface;

abstract class AbstractAction extends AbstractUrlElement implements ActionInterface
{
    /**
     * @return string
     */
    public static function getSuffix(): string
    {
        return self::SUFFIX;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    protected function definition(): DefinitionBuilderInterface
    {
        return new DefinitionBuilder;
    }
}
