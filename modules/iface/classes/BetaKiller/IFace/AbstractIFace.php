<?php

namespace BetaKiller\IFace;

use BetaKiller\Url\AbstractUrlElementInstance;

abstract readonly class AbstractIFace extends AbstractUrlElementInstance implements IFaceInterface
{
    /**
     * @return string
     */
    final public static function getSuffix(): string
    {
        return self::SUFFIX;
    }

    /**
     * @inheritDoc
     */
    public function getTemplatePath(): string
    {
        return '@ifaces'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, static::codename());
    }
}
