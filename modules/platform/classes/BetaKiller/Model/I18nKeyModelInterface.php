<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface I18nKeyModelInterface extends AbstractEntityInterface
{
    /**
     * @return string
     */
    public function getI18nKey(): string;
}
