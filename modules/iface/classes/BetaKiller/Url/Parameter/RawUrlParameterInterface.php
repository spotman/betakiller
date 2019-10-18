<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

interface RawUrlParameterInterface extends UrlParameterInterface
{
    public const CLASS_NS     = ['Url', 'Parameter'];
    public const CLASS_SUFFIX = 'UrlParameter';

    /**
     * Returns composed uri for current state
     *
     * @return string
     */
    public function exportUriValue(): string;

    /**
     * @return mixed
     */
    public function getValue();
}
