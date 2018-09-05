<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface LanguageInterface
{
    /**
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * @return null|string
     */
    public function getLocale(): ?string;
}
