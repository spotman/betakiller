<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

interface RawUrlParameterInterface extends UrlParameterInterface
{
    public const CLASS_NS     = ['Url', 'Parameter'];
    public const CLASS_SUFFIX = ''; // 'UrlParameter'

    /**
     * Creates instance from uri value
     *
     * @param string $value
     *
     * @return static
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    public static function fromUriValue(string $value): static;

    /**
     * Returns composed uri for current state
     *
     * @return string
     */
    public function exportUriValue(): string;
}
