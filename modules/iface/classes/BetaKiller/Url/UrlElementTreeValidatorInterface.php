<?php

declare(strict_types=1);

namespace BetaKiller\Url;

interface UrlElementTreeValidatorInterface
{
    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     *
     * @return void
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function validate(UrlElementTreeInterface $tree): void;
}
