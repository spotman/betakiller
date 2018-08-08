<?php
declare(strict_types=1);

namespace BetaKiller\Url\ElementFilter;

use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;

/**
 * Class MenuCodenameUrlElementFilter
 * Filter URL elements by menu codename
 *
 * @package BetaKiller\Url\ElementFilter
 */
class MenuCodenameUrlElementFilter implements UrlElementFilterInterface
{
    /**
     * IFace URL element menu codename
     *
     * @var string
     */
    private $menuCodename;

    /**
     * @param string $menuCodename Menu codename to be selected
     *
     * @throws \BetaKiller\Url\ElementFilter\UrlElementFilterException
     */
    public function __construct(string $menuCodename)
    {
        if (!$menuCodename) {
            throw new UrlElementFilterException('Menu codename can not be empty');
        }

        $this->menuCodename = mb_strtolower($menuCodename);
    }

    /**
     * Checking availability of IFace URL element by menu codename
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return bool
     */
    public function isAvailable(UrlElementInterface $urlElement): bool
    {
        return $urlElement instanceof IFaceModelInterface
            && $urlElement->getMenuName() === $this->menuCodename;
    }
}
