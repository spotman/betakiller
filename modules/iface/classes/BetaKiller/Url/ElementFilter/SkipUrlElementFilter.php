<?php
declare(strict_types=1);

namespace BetaKiller\Url\ElementFilter;

use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;

class SkipUrlElementFilter implements UrlElementFilterInterface
{
    /**
     * @var string[]
     */
    private $skip = [];

    /**
     * SkipUrlElementFilter constructor.
     *
     * @param string[] $skip
     */
    public function __construct(array $skip)
    {
        $this->skip = $skip;
    }

    /**
     * Checking availability of IFace URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return bool
     */
    public function isAvailable(UrlElementInterface $urlElement): bool
    {
        return !\in_array($urlElement->getCodename(), $this->skip, true);
    }
}
