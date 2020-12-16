<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Model\LanguageInterface;

interface RequestLanguageHelperInterface
{
    public function setLang(LanguageInterface $value): void;

    public function getLang(): LanguageInterface;
}
