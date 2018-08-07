<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Url\UrlElementFilterInterface;
use BetaKiller\Url\UrlElementFilterException;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\IFaceModelInterface;

/**
 * Filter of IFace URL element by menu codename
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
     * @throws \BetaKiller\Url\UrlElementFilterException
     */
    public function __construct(string $menuCodename)
    {
        $menuCodename = mb_strtolower(trim($menuCodename));
        if ($menuCodename === '') {
            throw new UrlElementFilterException('Menu codename can not be empty');
        }

        $this->menuCodename = $menuCodename;
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
        return
            $urlElement instanceof IFaceModelInterface
            && $urlElement->getMenuName() === $this->menuCodename;
    }
}
